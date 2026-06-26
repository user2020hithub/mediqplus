<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaEspera extends Model
{
    protected $table = 'lista_espera';
    protected $primaryKey = 'id_lista';
    public $timestamps = false;

    protected $fillable = [
        'id_paciente',
        'id_especialidad',
        'id_sede',
        'fecha_inicio_pref',
        'fecha_fin_pref',
        'estado',
        'consentimiento',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_inicio_pref' => 'date',
        'fecha_fin_pref' => 'date',
        'consentimiento' => 'boolean',
        'fecha_vencimiento' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad', 'id_especialidad');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    // ── Scope para el motor de reasignación (Fase 5) ────
    // Candidatos activos, no vencidos, ordenados por antigüedad de solicitud
    public function scopeCandidatosActivos($query, int $idEspecialidad, int $idSede)
    {
        return $query->where('id_especialidad', $idEspecialidad)
            ->where('id_sede', $idSede)
            ->where('estado', 'Activa')
            ->where('fecha_vencimiento', '>', now())
            ->orderBy('fecha_creacion', 'asc');
    }
}
