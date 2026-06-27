@extends('layouts.app')

@section('titulo', 'Mi Panel')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0 text-mediq-azul-oscuro">Mi Panel</h1>
    <a href="{{ route('paciente.citas.buscar') }}" class="btn btn-success">
        <i class="fa-solid fa-plus"></i> Reservar cita
    </a>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <h2 class="h5 fw-semibold mb-3"><i class="fa-solid fa-calendar-check text-primary"></i> Próximas citas</h2>

        @forelse($citasProximas ?? [] as $cita)
            <x-cita-card :cita="$cita" :mostrar-acciones="true">
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('paciente.triaje.formulario', $cita->id_cita) }}"
                       class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="fa-solid fa-clipboard-list"></i> Triaje
                    </a>
                    <form method="POST" action="{{ route('paciente.citas.cancelar', $cita->id_cita) }}"
                          class="flex-fill" onsubmit="return confirm('¿Confirma la cancelación de esta cita?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="motivo_cancelacion" value="Cancelado por el paciente">
                        <input type="hidden" name="confirma" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="fa-solid fa-xmark"></i> Cancelar
                        </button>
                    </form>
                </div>
            </x-cita-card>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-calendar-xmark fa-2x mb-2"></i>
                <p>No tiene citas próximas.</p>
            </div>
        @endforelse
    </div>

    <div class="col-lg-6 mb-4">
        <h2 class="h5 fw-semibold mb-3"><i class="fa-solid fa-clock-rotate-left text-muted"></i> Historial</h2>

        @forelse($historial ?? [] as $cita)
            <x-cita-card :cita="$cita" />
        @empty
            <div class="text-center py-5 text-muted">
                <p>Sin historial dentro del rango.</p>
            </div>
        @endforelse

        @if(method_exists($historial ?? null, 'links'))
            {{ $historial->links() }}
        @endif
    </div>
</div>
@endsection
