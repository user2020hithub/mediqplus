<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReprogramacionMasivaRequest;
use App\Services\ReprogramacionMasivaService;
use App\Exceptions\ReservaException;

class ContingenciaController extends Controller
{
    public function __construct(private ReprogramacionMasivaService $service) {}

    public function mostrarFormulario()
    {
        // NOTA: Medico NO tiene un scope activas() definido (solo
        // Especialidad y Sede lo tienen, desde la Fase 2) — se usa
        // where('estado', 'Activo') directamente.
        $medicos = \App\Models\Medico::where('estado', 'Activo')->get();
        $sedes = \App\Models\Sede::activas()->get();

        return view('admin.contingencia.crear', compact('medicos', 'sedes'));
    }

    /**
     * CU-12 — Punto de entrada HTTP. Mide el tiempo de ejecución para
     * el mensaje de éxito exigido por el paso 11 del SRS ("Tiempo total: Y segundos").
     */
    public function ejecutar(ReprogramacionMasivaRequest $request)
    {
        $inicio = microtime(true);

        try {
            $resultado = $this->service->ejecutar(
                $request->id_medico ? (int) $request->id_medico : null,
                $request->id_sede ? (int) $request->id_sede : null,
                $request->fecha_desde,
                $request->fecha_hasta,
                $request->motivo,
                $request->accion,
                $request->nueva_fecha,
                $request->nueva_hora_inicio,
                auth()->id()
            );

            $segundos = round(microtime(true) - $inicio, 1);

            return redirect()->route('admin.dashboard')->with('exito',
                "Reprogramación ejecutada exitosamente. {$resultado['cantidad']} pacientes notificados. Tiempo total: {$segundos} segundos.");

        } catch (ReservaException $e) {
            return back()->withInput()->with('error', $e->getMessage());

        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Error de conexión. Intente nuevamente en unos minutos.');
        }
    }
}
