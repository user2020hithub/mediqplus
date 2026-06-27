<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cita;
use App\Models\Agenda;
use App\Policies\CitaPolicy;
use App\Policies\AgendaPolicy;
use Illuminate\Support\Facades\Gate;
use App\Events\CitaCancelada;
use App\Listeners\ReasignacionListener;
use Illuminate\Support\Facades\Event;
use App\Events\SuscripcionListaEsperaCreada;
use App\Listeners\EnviarConfirmacionSuscripcionListener;
use App\Events\ReprogramacionMasivaEjecutada;
use App\Listeners\NotificarReprogramacionMasivaListener;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Cita::class, CitaPolicy::class);
        Gate::policy(Agenda::class, AgendaPolicy::class);
        Event::listen(CitaCancelada::class, ReasignacionListener::class);
        Event::listen(SuscripcionListaEsperaCreada::class, EnviarConfirmacionSuscripcionListener::class);
        Event::listen(ReprogramacionMasivaEjecutada::class, NotificarReprogramacionMasivaListener::class);
    }
}
