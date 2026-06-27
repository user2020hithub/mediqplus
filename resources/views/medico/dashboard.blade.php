@extends('layouts.app')

@section('titulo', 'Mi Agenda')

@section('contenido')
<h1 class="h3 fw-bold mb-4" style="color: var(--mediq-azul-oscuro);">Citas de Hoy</h1>

@php
    $citasHoy = \App\Models\Cita::where('id_medico', auth('web')->user()->medico->id_medico)
        ->where('fecha_cita', now()->toDateString())
        ->orderBy('hora_inicio')
        ->get();
@endphp

<div class="row">
    <div class="col-lg-8">
        @forelse($citasHoy as $cita)
            <x-cita-card :cita="$cita" :mostrar-acciones="true">
                @if($cita->estado === 'Triaje_Completado')
                    <a href="{{ route('medico.triaje.resumen', $cita->id_cita) }}"
                       class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fa-solid fa-clipboard-check"></i> Ver Triaje
                    </a>
                @endif
            </x-cita-card>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-mug-hot fa-2x mb-2"></i>
                <p>No tiene citas programadas para hoy.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
