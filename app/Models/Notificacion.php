<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false;

    protected $fillable = [
        'id_cita',
        'tipo_notificacion',
        'destinatario_email',
        'estado',
        'intentos',
        'fecha_programada',
        'fecha_envio',
        'mensaje_error',
        'prioridad',
    ];

    protected $casts = [
        'intentos' => 'integer',
        'fecha_programada' => 'datetime',
        'fecha_envio' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }

    // ── Scopes para el Job de envío en cola (Fase 5) ────
    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente')
            ->where('fecha_programada', '<=', now());
    }

    public function scopeFallidas($query)
    {
        return $query->where('estado', 'Fallido');
    }

    public function scopeAltaPrioridad($query)
    {
        return $query->where('prioridad', 'Alta');
    }

    public function marcarComoEnviada(): void
    {
        $this->update(['estado' => 'Enviado', 'fecha_envio' => now()]);
    }

    public function marcarComoFallida(string $error): void
    {
        $this->increment('intentos');
        $this->update(['estado' => 'Fallido', 'mensaje_error' => $error]);
    }
}
