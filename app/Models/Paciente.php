<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';
    protected $primaryKey = 'id_paciente';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tipo_documento',
        'numero_documento',
        'nombres_apellidos',
        'fecha_nacimiento',
        'sexo',
        'telefono_movil',
        'acepta_privacidad',
        'token_activacion',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'acepta_privacidad' => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_paciente', 'id_paciente');
    }

    public function listaEspera()
    {
        return $this->hasMany(ListaEspera::class, 'id_paciente', 'id_paciente');
    }

    // Edad calculada — útil para validar mayoría de edad (RF-01)
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}
