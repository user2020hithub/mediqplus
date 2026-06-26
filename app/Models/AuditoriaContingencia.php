<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaContingencia extends Model
{
    protected $table = 'auditoria_contingencias';
    protected $primaryKey = 'id_contingencia';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_responsable',
        'motivo',
        'detalle_motivo',
        'cantidad_afectada',
        'accion_ejecutada',
        'hash_integridad',
    ];

    protected $casts = [
        'cantidad_afectada' => 'integer',
        'fecha_ejecucion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_responsable', 'id_usuario');
    }

    // ── Helper para generar el hash de integridad ───────
    // Replica la lógica SHA2(CONCAT(...)) del script DML original
    public static function generarHash(int $idResponsable, string $motivo, int $cantidad): string
    {
        return hash('sha256', $idResponsable . $motivo . $cantidad . now());
    }

    public function scopePorMotivo($query, string $motivo)
    {
        return $query->where('motivo', $motivo);
    }
}
