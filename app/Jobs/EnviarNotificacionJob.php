<?php

namespace App\Jobs;

use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

// Este Job envuelve el envío real y actualiza la tabla "notificaciones"
// (definida desde la Fase 2) según el resultado — separa la responsabilidad
// de "enviar el correo" de la de "registrar el resultado en BD".
class EnviarNotificacionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Reintento exponencial: 3 intentos con 5 min y 15 min de espera
    // (paso 8 del SRS de CU-09).
    public int $tries = 3;
    public array $backoff = [300, 900]; // segundos: 5 min, luego 15 min

    public function __construct(
        public Notificacion $notificacion,
        public \Illuminate\Mail\Mailable $mailable
    ) {}

    public function handle(): void
    {
        // ── Flujo alternativo 4a del SRS: correo marcado Inválido/Rebotado ──
        if (in_array($this->notificacion->cita->paciente->estado_correo ?? 'Activo', ['Invalido', 'Rebotado'])) {
            $this->notificacion->update([
                'estado' => 'Fallido',
                'mensaje_error' => 'Correo marcado como inválido o rebotado.'
            ]);
            return;
        }

        Mail::to($this->notificacion->destinatario_email)->send($this->mailable);

        $this->notificacion->update([
            'estado' => 'Enviado',
            'fecha_envio' => now(),
        ]);
    }

    // Se ejecuta automáticamente si el Job falla las 3 veces (paso 9 del SRS)
    public function failed(\Throwable $exception): void
    {
        $this->notificacion->update([
            'estado' => 'Fallido',
            'mensaje_error' => $exception->getMessage(),
        ]);

        \Log::error('[CU-09] Notificación falló definitivamente', [
            'id_notificacion' => $this->notificacion->id_notificacion,
        ]);
    }
}
