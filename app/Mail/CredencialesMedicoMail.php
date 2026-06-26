<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesMedicoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Usuario $usuario,
        public string $passwordTemporal,
        public string $tokenActivacion
    ) {
        $this->onQueue('alta');
    }

    public function build(): self
    {
        $medico = $this->usuario->medico;

        return $this
            ->subject('Sus credenciales de acceso — MEDIQ+ TuSalud')
            ->view('emails.credenciales-medico')
            ->with([
                'nombreMedico' => $medico?->nombres_apellidos ?? 'Profesional',
                'correo' => $this->usuario->correo_electronico,
                'passwordTemporal' => $this->passwordTemporal,
                'urlLogin' => route('auth.login'),
            ]);
    }
}
