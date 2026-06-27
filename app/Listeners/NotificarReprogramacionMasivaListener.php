<?php

namespace App\Listeners;

use App\Events\ReprogramacionMasivaEjecutada;
use App\Models\Cita;
use App\Mail\ReprogramacionMasivaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

// ShouldQueue + cola "alta" (paso 8 del SRS: "evento asíncrono de ALTA
// prioridad") — igual prioridad que el motor de reasignación (Fase 5),
// porque ambos son comunicaciones urgentes hacia el paciente.
class NotificarReprogramacionMasivaListener implements ShouldQueue
{
    public string $queue = 'alta';

    public function handle(ReprogramacionMasivaEjecutada $event): void
    {
        // ── Paso 9 del SRS: procesar la cola sin bloquear al admin ──
        // Cada cita genera su propio correo PERSONALIZADO — se itera
        // aquí (dentro del Listener, ya async) en vez de en el Service
        // (que corre de forma síncrona dentro de la petición HTTP del admin).
        $citas = Cita::whereIn('id_cita', $event->citaIds)
            ->with(['paciente.usuario', 'medico', 'sede'])
            ->get();

        foreach ($citas as $cita) {
            // ── Excepción E-01 del SRS: si el SMTP falla, NO se revierte
            // la reprogramación — solo se registra la alerta y se
            // confía en el reintento automático de Laravel Queue.
            try {
                Mail::to($cita->paciente->usuario->correo_electronico)
                    ->queue(new ReprogramacionMasivaMail($cita, $event->motivo, $event->accion));
            } catch (\Exception $e) {
                \Log::critical('[CU-12] Fallo al notificar reprogramación masiva', [
                    'id_cita' => $cita->id_cita,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
