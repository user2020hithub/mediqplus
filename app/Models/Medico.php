<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medicos';
    protected $primaryKey = 'id_medico';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_especialidad',
        'nombres_apellidos',
        'dni',
        'colegiatura',
        'telefono',
        'estado',
        'token_activacion',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad', 'id_especialidad');
    }

    // Relación N:M a través de la tabla pivote medico_sede
    public function sedes()
    {
        return $this->belongsToMany(Sede::class, 'medico_sede', 'id_medico', 'id_sede')
            ->withPivot('activo');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'id_medico', 'id_medico');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_medico', 'id_medico');
    }
}
