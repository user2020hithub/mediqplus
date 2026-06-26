<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false; // Se usan fecha_creacion / fecha_actualizacion custom

    protected $fillable = [
        'rol', 'correo_electronico', 'password_hash', 'estado_cuenta',
        'habilitado_2fa', 'totp_secret',
    ];

    protected $hidden = ['password_hash', 'totp_secret', 'codigo_respaldo'];

    protected $casts = [
        'habilitado_2fa' => 'boolean',
        'codigo_respaldo_usado' => 'boolean',
        'ultimo_login' => 'datetime',
        'bloqueado_hasta' => 'datetime',
        'fecha_creacion' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────
    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'id_usuario', 'id_usuario');
    }

    public function medico()
    {
        return $this->hasOne(Medico::class, 'id_usuario', 'id_usuario');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id_usuario');
    }

    // ── Para autenticación con Sanctum/Auth ─────────
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Si Laravel necesita saber el nombre de la PK:
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }
}
