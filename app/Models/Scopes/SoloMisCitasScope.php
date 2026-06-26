<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

// Este Global Scope es la defensa principal contra ataques IDOR
// (Insecure Direct Object Reference): aunque alguien manipule la URL
// para pedir datos de otro paciente, esta clase se asegura de que
// el WHERE siempre use el ID de la SESIÓN real, no el de la petición.
class SoloMisCitasScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $usuario = auth('web')->user();

        // Si no hay usuario autenticado o no es paciente, no se aplica el
        // filtro (los roles admin/medico usan sus propios filtros explícitos
        // dentro de cada Controller, no este Scope automático).
        if (!$usuario || $usuario->rol !== 'paciente') {
            return;
        }

        // Esto es lo que impide el ataque IDOR: incluso si alguien manipula
        // la URL para pedir ?id_paciente=99, este WHERE siempre se agrega
        // usando el ID real de la sesión actual, ignorando cualquier valor
        // que venga desde fuera.
        $builder->where('id_paciente', $usuario->paciente?->id_paciente);
    }
}
