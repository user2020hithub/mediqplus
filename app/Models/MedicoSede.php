<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoSede extends Model
{
    protected $table = 'medico_sede';
    protected $primaryKey = 'id_medico_sede';
    public $timestamps = false;

    protected $fillable = [
        'id_medico',
        'id_sede',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
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
}
