<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    protected $primaryKey = 'id_auditoria';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'tabla_afectada',
        'id_registro',
        'detalle_json',
        'ip_origen',
        'user_agent',
    ];

    protected $casts = [
        'detalle_json' => 'array',
        'timestamp_accion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    // ── Helper estático para registrar eventos rápido ───
    // Uso: Auditoria::registrar($usuarioId, 'LOGIN_EXITOSO', 'usuarios', $usuarioId, [...]);
    public static function registrar(
        ?int $idUsuario,
        string $accion,
        ?string $tabla = null,
        ?int $idRegistro = null,
        ?array $detalle = null
    ): self {
        return self::create([
            'id_usuario' => $idUsuario,
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'id_registro' => $idRegistro,
            'detalle_json' => $detalle,
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function scopePorAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }
}
