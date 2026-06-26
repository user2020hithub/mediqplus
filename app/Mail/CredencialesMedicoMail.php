<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// Mailable que entrega las credenciales temporales al médico recién
// registrado (CU-05, paso E-01). Se conecta de verdad en la Fase 5;
// en la Fase 4 esto era solo un Log::info() placeholder.
class CredencialesMedicoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $passwordTemporal,
        public string $tokenActivacion
    ) {
        // Prioridad normal (no es tan urgente como una oferta de reasignación)
        $this->onQueue('alta');
    }

    public function build(): self
    {
        $medico = $this->usuario->medico;

        return $this
            ->subject('Bienvenido a MEDIQ+ - Sus credenciales de acceso — MEDIQ+ TuSalud')
            ->view('emails.credenciales-medico')
            ->with([
                // RNF-OTR-01: solo datos estrictamente necesarios para activar
                // la cuenta — nunca se incluye información clínica (no aplica
                // aquí, pero es el mismo principio usado en OfertaReasignacionMail).
                'nombreMedico' => $medico?->nombres_apellidos ?? 'Profesional',
                'correo' => $this->usuario->correo_electronico,
                'passwordTemporal' => $this->passwordTemporal,
                // El token de activación se valida contra fecha_creacion + 24h
                // (igual que se definió en el MedicoController de la Fase 4) —
                // por eso aquí solo se construye la URL, no se repite esa lógica.
                'urlActivacion' => route('auth.activar', $this->tokenActivacion), //'urlLogin' => route('auth.login'),
            ]);
    }
}
