@props(['cita', 'mostrarAcciones' => false])

{{--
    Componente reutilizable para mostrar una cita con su badge de estado
    semaforico. $estadoClase convierte el estado de BD (con guion bajo)
    en una clase CSS valida (sin caracteres especiales problematicos).
--}}
@php
    $estadoClase = strtolower($cita->estado);
    $iconos = [
        'confirmada' => 'fa-circle-check',
        'pendiente_confirmacion' => 'fa-clock',
        'pendiente_reprogramacion' => 'fa-rotate',
        'cancelada' => 'fa-circle-xmark',
        'atendida' => 'fa-circle-check',
        'no_show' => 'fa-circle-exclamation',
        'triaje_completado' => 'fa-clipboard-check',
    ];
    $icono = $iconos[$estadoClase] ?? 'fa-circle';
@endphp

<div class="tarjeta-cita card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <strong>{{ $cita->codigo_cita }}</strong>
                <div class="text-muted small">{{ $cita->fecha_cita->format('d/m/Y') }} · {{ $cita->hora_inicio }}</div>
            </div>
            <span class="badge badge-estado-{{ $estadoClase }} px-3 py-2">
                <i class="fa-solid {{ $icono }}"></i> {{ str_replace('_', ' ', $cita->estado) }}
            </span>
        </div>

        <p class="mb-1"><i class="fa-solid fa-user-doctor text-muted"></i> {{ $cita->medico->nombres_apellidos ?? '—' }}</p>
        <p class="mb-2"><i class="fa-solid fa-location-dot text-muted"></i> {{ $cita->sede->nombre ?? '—' }}</p>

        @if($mostrarAcciones)
            {{ $slot }}
        @endif
    </div>
</div>
