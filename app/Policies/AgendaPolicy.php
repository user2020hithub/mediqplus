<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Agenda;

class AgendaPolicy
{
    // Un médico solo gestiona SU PROPIA agenda
    public function gestionar(Usuario $usuario, Agenda $agenda): bool
    {
        if ($usuario->rol === 'medico') {
            return $usuario->medico?->id_medico === $agenda->id_medico;
        }

        return $usuario->rol === 'admin';
    }
}
