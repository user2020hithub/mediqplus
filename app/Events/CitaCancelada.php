<?php

namespace App\Events;

use App\Models\Cita;
use Illuminate\Foundation\Events\Dispatchable;

// Este evento es el "disparador" que conecta CU-07 con CU-08.
// Cuando se cancela una cita, se dispara este evento; el Listener
// registrado en EventServiceProvider lo captura y activa el motor.
class CitaCancelada
{
    use Dispatchable;

    public function __construct(public Cita $cita) {}
}
