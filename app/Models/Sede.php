<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sedes';
    protected $primaryKey = 'id_sede';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'distrito',
        'telefono',
        'estado',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'id_sede', 'id_sede');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_sede', 'id_sede');
    }

    public function listaEspera()
    {
        return $this->hasMany(ListaEspera::class, 'id_sede', 'id_sede');
    }

    // Relación N:M a través de la tabla pivote medico_sede
    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'medico_sede', 'id_sede', 'id_medico')
            ->withPivot('activo');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'Activo');
    }
}
