<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Cita;

class CitaPolicy
{
    // Un paciente solo puede ver SUS PROPIAS citas (RNF-SEGU-07)
    public function ver(Usuario $usuario, Cita $cita): bool
    {
        if ($usuario->rol === 'paciente') {
            return $usuario->paciente?->id_paciente === $cita->id_paciente;
        }

        if ($usuario->rol === 'medico') {
            return $usuario->medico?->id_medico === $cita->id_medico;
        }

        // Admin tiene visibilidad total
        return $usuario->rol === 'admin';
    }

    // Solo el paciente propietario o un admin puede cancelar
    public function cancelar(Usuario $usuario, Cita $cita): bool
    {
        if ($usuario->rol === 'admin') {
            return true;
        }

        return $usuario->rol === 'paciente'
            && $usuario->paciente?->id_paciente === $cita->id_paciente;
    }
}
