<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\SoloMisCitasScope;

class Cita extends Model
{
    protected $table = 'citas';
    protected $primaryKey = 'id_cita';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'codigo_cita',
        'id_paciente',
        'id_medico',
        'id_sede',
        'id_agenda',
        'fecha_cita',
        'hora_inicio',
        'hora_fin',
        'estado',
        'motivo_consulta',
    ];

    protected $casts = [
        'fecha_cita' => 'date',
        'es_cancelacion_tardia' => 'boolean',
        'fecha_cancelacion' => 'datetime',
    ];

    // Este método se ejecuta automáticamente cada vez que el modelo Cita
    // se "arranca" — es el lugar correcto para registrar Global Scopes.
    // Global Scopes son filtros que se aplican a todas las consultas del modelo,
    // no solo a las que explícitamente usan el método scope.
    protected static function booted(): void
    {
        static::addGlobalScope(new SoloMisCitasScope());
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'id_medico', 'id_medico');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'id_agenda', 'id_agenda');
    }

    public function triaje()
    {
        return $this->hasOne(Triaje::class, 'id_cita', 'id_cita');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_cita', 'id_cita');
    }

    // Scope útil para el motor de reasignación (Fase 5)
    public function scopeCancelables($query)
    {
        return $query->whereIn('estado', ['Pendiente_Confirmacion', 'Confirmada']);
    }
}
