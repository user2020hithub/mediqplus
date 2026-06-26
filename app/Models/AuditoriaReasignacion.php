<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaReasignacion extends Model
{
    protected $table = 'auditoria_reasignacion';
    protected $primaryKey = 'id_auditoria_reas';
    public $timestamps = false;

    protected $fillable = [
        'id_cita_origen',
        'id_candidato',
        'score_calculado',
        'intento_numero',
        'resultado',
    ];

    protected $casts = [
        'score_calculado' => 'decimal:2',
        'intento_numero' => 'integer',
        'timestamp_accion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function citaOrigen()
    {
        return $this->belongsTo(Cita::class, 'id_cita_origen', 'id_cita');
    }

    public function candidato()
    {
        return $this->belongsTo(Paciente::class, 'id_candidato', 'id_paciente');
    }

    // ── Scopes útiles para reportes del motor (Fase 5) ──
    public function scopeDeCita($query, int $idCitaOrigen)
    {
        return $query->where('id_cita_origen', $idCitaOrigen)
            ->orderBy('intento_numero', 'asc');
    }

    public function scopeAceptadas($query)
    {
        return $query->where('resultado', 'Aceptada');
    }

    public function scopeSinCandidatos($query)
    {
        return $query->where('resultado', 'Sin_Candidatos');
    }
}
