<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ReprogramacionMasivaEjecutada
{
    use Dispatchable;

    public function __construct(
        public array $citaIds,
        public string $motivo,
        public string $accion
    ) {}
}
