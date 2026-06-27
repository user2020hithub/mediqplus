<?php

namespace App\Mail;

use App\Models\ListaEspera;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmacionListaEsperaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ListaEspera $suscripcion) {}

    public function build(): self
    {
        return $this
            ->subject('Suscripción a lista de espera confirmada — MEDIQ+')
            ->view('emails.confirmacion-lista-espera')
            ->with([
                'nombrePaciente' => $this->suscripcion->paciente->nombres_apellidos,
                'nombreEspecialidad' => $this->suscripcion->especialidad->nombre,
                'nombreSede' => $this->suscripcion->sede->nombre,
                // Paso 10 del SRS: enlaces para renovar o cancelar la suscripción
                'urlCancelar' => route('paciente.lista-espera.cancelar', $this->suscripcion->id_lista),
            ]);
    }
}
