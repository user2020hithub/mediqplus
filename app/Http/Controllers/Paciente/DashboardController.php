<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Gracias al SoloMisCitasScope, esta consulta YA está filtrada
        // automáticamente por el paciente autenticado — no se necesita
        // agregar ->where('id_paciente', ...) manualmente aquí.

        // Validación de rango histórico: máximo 6 meses hacia atrás
        // (flujo alternativo 4b del SRS). Si el paciente pide más, se
        // recorta silenciosamente al máximo permitido.
        $fechaDesde = $request->fecha_desde
            ? max($request->fecha_desde, now()->subMonths(6)->toDateString())
            : now()->subMonths(6)->toDateString();

        $citasProximas = Cita::with(['medico', 'sede'])
            ->whereIn('estado', ['Pendiente_Confirmacion', 'Confirmada'])
            ->where('fecha_cita', '>=', now()->toDateString())
            ->orderBy('fecha_cita')
            ->get();

        $historial = Cita::with(['medico', 'sede'])
            ->where('fecha_cita', '<', now()->toDateString())
            ->where('fecha_cita', '>=', $fechaDesde)
            ->orderByDesc('fecha_cita')
            ->paginate(10);

        return view('paciente.dashboard', compact('citasProximas', 'historial'));
    }
}
