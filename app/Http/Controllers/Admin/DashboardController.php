<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $fechaInicio = $request->fecha_inicio ?? now()->subDays(30)->toDateString();
        $fechaFin = $request->fecha_fin ?? now()->toDateString();

        // Validación de rango: máximo 90 días (flujo alternativo 4c del SRS)
        if (now()->parse($fechaInicio)->diffInDays($fechaFin) > 90) {
            return back()->with(
                'error',
                'El rango de búsqueda no puede exceder los 90 días para garantizar el rendimiento del sistema.'
            );
        }

        // KPIs calculados directamente en el motor de BD (paso 6 del SRS),
        // evitando traer miles de filas a PHP solo para contar — esto es
        // mucho más rápido en bases de datos grandes.
        $kpis = Cita::whereBetween('fecha_cita', [$fechaInicio, $fechaFin])
            ->selectRaw("
                COUNT(*) as total_citas,
                SUM(CASE WHEN estado = 'Confirmada' THEN 1 ELSE 0 END) as confirmadas,
                SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas,
                SUM(CASE WHEN estado = 'Atendida' THEN 1 ELSE 0 END) as atendidas,
                SUM(CASE WHEN estado = 'No_Show' THEN 1 ELSE 0 END) as no_show
            ")
            ->first();

        $tasaConfirmacion = $kpis->total_citas > 0
            ? round(($kpis->confirmadas / $kpis->total_citas) * 100, 1)
            : 0;

        // Listado filtrable de citas (SIN el Global Scope — el admin ve todas,
        // porque el rol no es "paciente" y el Scope se desactiva a sí mismo).
        $citas = Cita::with(['paciente', 'medico', 'sede'])
            ->whereBetween('fecha_cita', [$fechaInicio, $fechaFin])
            ->when($request->id_sede, fn($q) => $q->where('id_sede', $request->id_sede))
            ->when($request->id_medico, fn($q) => $q->where('id_medico', $request->id_medico))
            ->paginate(20);

        return view('admin.dashboard', compact('kpis', 'tasaConfirmacion', 'citas'));
    }
}
