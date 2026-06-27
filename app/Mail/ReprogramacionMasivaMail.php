<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReprogramacionMasivaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Cita $cita,
        public string $motivo,
        public string $accion
    ) {
        $this->onQueue('alta');
    }

    public function build(): self
    {
        return $this
            ->subject('Cambio importante en su cita — MEDIQ+ TuSalud')
            ->view('emails.reprogramacion-masiva')
            ->with([
                'nombrePaciente' => $this->cita->paciente->nombres_apellidos,
                'nombreMedico' => $this->cita->medico->nombres_apellidos,
                'nombreSede' => $this->cita->sede->nombre,
                'motivo' => $this->motivo,
                'esOpcionA' => $this->accion === 'A',
                'nuevaFecha' => $this->cita->fecha_cita,
                'nuevaHora' => $this->cita->hora_inicio,
                'urlReprogramar' => $this->accion === 'B'
                    ? route('paciente.citas.reprogramar', $this->cita->token_reprogramacion)
                    : null,
            ]);
    }
}
