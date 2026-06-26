<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Medico;
use App\Models\Auditoria;
use App\Services\AgendaValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    // Inyección de dependencia: Laravel resuelve automáticamente el Service
    public function __construct(private AgendaValidationService $validador) {}

    /**
     * CU-06 — Crear una nueva franja horaria (Regular o Excepción).
     * Incluye AMBAS validaciones: solapamiento Y margen de traslado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_medico' => ['required', 'exists:medicos,id_medico'],
            'id_sede' => ['required', 'exists:sedes,id_sede'],
            'tipo' => ['required', 'in:Regular,Excepcion'],
            'dia_semana' => ['required_if:tipo,Regular', 'nullable', 'integer', 'between:1,7'],
            'fecha_especifica' => ['required_if:tipo,Excepcion', 'nullable', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'duracion_minutos' => ['required', 'in:15,20,30,45,60'],
        ]);

        // ── Validación 1: solapamiento (flujo alternativo 4a del SRS) ────
        $solapa = $this->validador->haySolapamiento(
            (int) $request->id_medico,
            $request->dia_semana ? (int) $request->dia_semana : null,
            $request->fecha_especifica,
            $request->hora_inicio . ':00',
            $request->hora_fin . ':00',
        );

        if ($solapa) {
            return back()->withInput()->with(
                'error',
                'El horario seleccionado se superpone con una agenda ya configurada para este médico.'
            );
        }

        // ── Validación 2: margen de traslado de 120 min (flujo 6b del SRS) ──
        $cumpleMargen = $this->validador->cumpleMargenTraslado(
            (int) $request->id_medico,
            (int) $request->id_sede,
            $request->dia_semana ? (int) $request->dia_semana : null,
            $request->fecha_especifica,
            $request->hora_inicio . ':00',
            $request->hora_fin . ':00',
        );

        if (!$cumpleMargen) {
            return back()->withInput()->with(
                'error',
                'El horario no es viable: no se respeta el tiempo mínimo de traslado (120 min) entre la sede anterior y la nueva.'
            );
        }

        // Si ambas validaciones pasaron, se crea el registro dentro de
        // una transacción ACID (paso 3 del SRS).
        DB::beginTransaction();
        try {
            $agenda = Agenda::create([
                'id_medico' => $request->id_medico,
                'id_sede' => $request->id_sede,
                'tipo' => $request->tipo,
                'dia_semana' => $request->dia_semana,
                'fecha_especifica' => $request->fecha_especifica,
                'hora_inicio' => $request->hora_inicio . ':00',
                'hora_fin' => $request->hora_fin . ':00',
                'duracion_minutos' => $request->duracion_minutos,
                'estado' => 'Disponible',
            ]);

            Auditoria::registrar(auth()->id(), 'CREAR_AGENDA', 'agenda', $agenda->id_agenda, [
                'nuevo' => $agenda->toArray(),
            ]);

            DB::commit();
            return back()->with('exito', 'Agenda configurada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Error de conexión. Intente nuevamente en unos minutos.');
        }
    }

    /**
     * CU-06 — Bloquear un rango de agenda (ej. vacaciones), validando que
     * no existan citas activas en ese rango (flujo alternativo 6a del SRS).
     */
    public function bloquear(Request $request, Agenda $agenda)
    {
        $request->validate(['motivo_bloqueo' => ['required', 'string', 'max:255']]);

        // Se verifica ANTES de bloquear si hay citas que se verían afectadas
        $citasAfectadas = \App\Models\Cita::where('id_agenda', $agenda->id_agenda)
            ->whereIn('estado', ['Pendiente_Confirmacion', 'Confirmada'])
            ->count();

        if ($citasAfectadas > 0) {
            return back()->with(
                'error',
                "No se puede modificar este horario porque existen {$citasAfectadas} citas programadas. Debe reprogramarlas o cancelarlas primero."
            );
        }

        $agenda->update(['estado' => 'Bloqueado', 'motivo_bloqueo' => $request->motivo_bloqueo]);

        Auditoria::registrar(auth()->id(), 'BLOQUEAR_AGENDA', 'agenda', $agenda->id_agenda, [
            'motivo' => $request->motivo_bloqueo,
        ]);

        return back()->with('exito', 'Franja horaria bloqueada correctamente.');
    }
}
