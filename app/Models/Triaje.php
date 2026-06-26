<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Triaje extends Model
{
    protected $table = 'triaje';
    protected $primaryKey = 'id_triaje';
    public $timestamps = false;

    protected $fillable = [
        'id_cita',
        'motivo_consulta',
        'sintomas',
        'intensidad',
        'tiene_alergias',
        'detalle_alergias',
        'medicamentos_actuales',
        'acepta_disclaimer',
    ];

    protected $casts = [
        'sintomas' => 'array',
        'tiene_alergias' => 'boolean',
        'acepta_disclaimer' => 'boolean',
    ];

    // ── Cifrado AES-256 transparente (RNF-SEGU-02) ──────
    public function setDetalleAlergiasAttribute($value)
    {
        $this->attributes['detalle_alergias'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDetalleAlergiasAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setMedicamentosActualesAttribute($value)
    {
        $this->attributes['medicamentos_actuales'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getMedicamentosActualesAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'id_cita', 'id_cita');
    }
}
