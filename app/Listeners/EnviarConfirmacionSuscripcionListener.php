<?php

namespace App\Listeners;

use App\Events\SuscripcionListaEsperaCreada;
use App\Mail\ConfirmacionListaEsperaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

// ShouldQueue + Excepción E-01 del SRS: si el envío falla, Laravel
// reintenta con retraso EXPONENCIAL (igual que EnviarNotificacionJob
// de la Fase 5) — la suscripción YA quedó guardada, así que el
// paciente no pierde su lugar aunque el correo tarde en llegar.
class EnviarConfirmacionSuscripcionListener implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [300, 900]; // 5 min, luego 15 min — mismo patrón de CU-09

    public function handle(SuscripcionListaEsperaCreada $event): void
    {
        Mail::to($event->suscripcion->paciente->usuario->correo_electronico)
            ->send(new ConfirmacionListaEsperaMail($event->suscripcion));
    }
}
