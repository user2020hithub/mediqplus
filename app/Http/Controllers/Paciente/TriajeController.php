<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Http\Requests\TriajeRequest;
use App\Models\Cita;
use App\Models\Triaje;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TriajeController extends Controller
{
    /**
     * Muestra el formulario, validando primero la ventana temporal
     * (T-24h a T-1h, paso 2 del SRS) y la propiedad de la cita (2b).
     */
    public function mostrarFormulario(Request $request, Cita $cita)
    {
        // ── Flujo alternativo 2b del SRS: prevención de IDOR ─────────
        if ($cita->id_paciente !== $request->user()->paciente->id_paciente) {
            abort(403, 'Acceso denegado.');
        }

        $fechaHoraCita = \Carbon\Carbon::parse(
            $cita->fecha_cita->format('Y-m-d') . ' ' . $cita->hora_inicio
        );

        $horasHastaLaCita = now()->diffInHours($fechaHoraCita, false);

        // ── Flujo alternativo 2a del SRS: fuera de la ventana permitida ──
        if ($horasHastaLaCita > 24 || $horasHastaLaCita < 1) {
            return view('paciente.triaje.no-disponible');
        }

        return view('paciente.triaje.formulario', compact('cita'));
    }

    /**
     * CU-10 — Guarda el triaje, cifra los campos sensibles (vía el Model,
     * Fase 2) y actualiza el estado de la cita, todo en una transacción ACID.
     */
    public function guardar(TriajeRequest $request, Cita $cita)
    {
        if ($cita->id_paciente !== $request->user()->paciente->id_paciente) {
            abort(403, 'Acceso denegado.');
        }

        try {
            DB::beginTransaction();

            // El cifrado AES-256 de detalle_alergias y medicamentos_actuales
            // ocurre AUTOMÁTICAMENTE aquí, gracias a los mutators ya definidos
            // en app/Models/Triaje.php desde la Fase 2 (setDetalleAlergiasAttribute
            // y setMedicamentosActualesAttribute) — no se repite esa lógica aquí.
            Triaje::create([
                'id_cita' => $cita->id_cita,
                'motivo_consulta' => $request->motivo_consulta,
                'sintomas' => $request->sintomas,
                'intensidad' => $request->intensidad,
                'tiene_alergias' => $request->tiene_alergias,
                'detalle_alergias' => $request->detalle_alergias,
                'medicamentos_actuales' => $request->medicamentos_actuales,
                'acepta_disclaimer' => true,
            ]);

            $cita->update(['estado' => 'Triaje_Completado']);

            Auditoria::registrar(
                $request->user()->id_usuario,
                'TRIAJE_COMPLETADO',
                'citas',
                $cita->id_cita,
                ['acepto_disclaimer' => true]
            );

            DB::commit();

            return redirect()->route('paciente.dashboard')->with(
                'exito',
                'Triaje completado exitosamente. Esta información ayudará a su médico a preparar su consulta.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with(
                'error',
                'Error al guardar la información. Por favor, intente nuevamente.'
            );
        }
    }
}
