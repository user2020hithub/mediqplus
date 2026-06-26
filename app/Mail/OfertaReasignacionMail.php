<?php

namespace App\Mail;

use App\Models\Cita;
use App\Models\ListaEspera;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfertaReasignacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Cita $citaOrigen,
        public ListaEspera $candidato,
        public string $token
    ) {
        // Prioridad ALTA en la cola (paso 2 del SRS de CU-09)
        $this->onQueue('alta');
    }

    public function build(): self
    {
        return $this
            ->subject('¡Tenemos un cupo disponible para usted! — MEDIQ+ TuSalud')
            ->view('emails.oferta-reasignacion')
            ->with([
                // SOLO variables permitidas (RNF-OTR-01) — JAMÁS se incluyen
                // datos clínicos (motivo de consulta, síntomas, alergias).
                'nombrePaciente' => $this->candidato->paciente->nombres_apellidos,
                'fechaCita' => $this->citaOrigen->agenda->fecha_especifica
                    ?? 'próxima fecha disponible según el horario del médico',
                'horaCita' => $this->citaOrigen->agenda->hora_inicio,
                'nombreMedico' => $this->citaOrigen->medico->nombres_apellidos,
                'nombreSede' => $this->citaOrigen->sede->nombre,
                'urlAceptar' => route('oferta.aceptar', $this->token),
                'urlRechazar' => route('oferta.rechazar', $this->token),
            ]);
    }
}
