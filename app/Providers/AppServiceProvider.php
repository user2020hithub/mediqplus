<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cita;
use App\Models\Agenda;
use App\Policies\CitaPolicy;
use App\Policies\AgendaPolicy;
use Illuminate\Support\Facades\Gate;

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
        //
        Gate::policy(Cita::class, CitaPolicy::class);
        Gate::policy(Agenda::class, AgendaPolicy::class);
    }
}
