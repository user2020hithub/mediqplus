<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    protected $primaryKey = 'id_especialidad';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_default',
        'estado',
    ];

    protected $casts = [
        'duracion_default' => 'integer',
        'fecha_creacion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function medicos()
    {
        return $this->hasMany(Medico::class, 'id_especialidad', 'id_especialidad');
    }

    public function listaEspera()
    {
        return $this->hasMany(ListaEspera::class, 'id_especialidad', 'id_especialidad');
    }

    // ── Scope útil para formularios de selección ────
    public function scopeActivas($query)
    {
        return $query->where('estado', 'Activo');
    }
}
