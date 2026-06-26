<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $table = 'agenda';
    protected $primaryKey = 'id_agenda';
    public $timestamps = false;

    protected $fillable = [
        'id_medico',
        'id_sede',
        'tipo',
        'dia_semana',
        'fecha_especifica',
        'hora_inicio',
        'hora_fin',
        'duracion_minutos',
        'estado',
        'motivo_bloqueo',
    ];

    protected $casts = [
        'fecha_especifica' => 'date',
        'duracion_minutos' => 'integer',
        'dia_semana' => 'integer',
        'fecha_creacion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'id_medico', 'id_medico');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_agenda', 'id_agenda');
    }

    // ── Scopes útiles para el motor de slots (Fase 4/5) ──
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', 'Disponible');
    }

    public function scopeRegulares(Builder $query): Builder
    {
        return $query->where('tipo', 'Regular');
    }

    public function scopeExcepciones(Builder $query): Builder
    {
        return $query->where('tipo', 'Excepcion');
    }
    
}
