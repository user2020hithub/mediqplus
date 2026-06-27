<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;

class ReprogramacionController extends Controller
{
    /**
     * El paciente accede SIN sesión activa (vía enlace de correo),
     * igual que en OfertaReasignacionController de la Fase 5 — la
     * seguridad la da el token de 64 caracteres, no la sesión.
     */
    public function mostrarOpciones(string $token)
    {
        $cita = Cita::where('token_reprogramacion', $token)
            ->where('estado', 'Pendiente_Reprogramacion')
            ->first();

        // ── Validación de expiración de 72h ──────────────────────────
        // Se deriva de fecha_actualizacion, igual que el token de
        // activación de médicos (Fase 4) — sin columna nueva en BD.
        if (!$cita || $cita->fecha_actualizacion->addHours(72)->isPast()) {
            return view('paciente.reprogramacion.expirado');
        }

        // Reutiliza la misma consulta de disponibilidad de CitaController
        // (Fase 4) — filtrando por la MISMA especialidad del médico original.
        $slots = \App\Models\Agenda::disponibles()
            ->whereHas('medico', fn($q) => $q->where('id_especialidad', $cita->medico->id_especialidad))
            ->with(['medico', 'sede'])
            ->get();

        return view('paciente.reprogramacion.elegir-slot', compact('cita', 'slots', 'token'));
    }

    public function confirmar(Request $request, string $token)
    {
        $request->validate(['id_agenda' => ['required', 'exists:agenda,id_agenda']]);

        $cita = Cita::where('token_reprogramacion', $token)
            ->where('estado', 'Pendiente_Reprogramacion')
            ->first();

        if (!$cita || $cita->fecha_actualizacion->addHours(72)->isPast()) {
            return view('paciente.reprogramacion.expirado');
        }

        $nuevaAgenda = \App\Models\Agenda::where('id_agenda', $request->id_agenda)
            ->lockForUpdate()
            ->first();

        if ($nuevaAgenda->estado !== 'Disponible') {
            return back()->with('error', 'Ese horario ya no está disponible. Por favor, elija otro.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($cita, $nuevaAgenda) {
            $nuevaAgenda->update(['estado' => 'Reservado']);

            $cita->update([
                'id_agenda' => $nuevaAgenda->id_agenda,
                'id_medico' => $nuevaAgenda->id_medico,
                'id_sede' => $nuevaAgenda->id_sede,
                'fecha_cita' => $nuevaAgenda->fecha_especifica ?? now()->toDateString(),
                'hora_inicio' => $nuevaAgenda->hora_inicio,
                'hora_fin' => $nuevaAgenda->hora_fin,
                'estado' => 'Confirmada',
                'token_reprogramacion' => null, // se invalida tras usarse
            ]);
        });

        return view('paciente.reprogramacion.exito', compact('cita'));
    }
}
