@extends('layouts.app')

@section('titulo', 'Buscar Disponibilidad')

@section('contenido')
    <h1 class="h3 fw-bold mb-4" style="color: var(--mediq-azul-oscuro);">Buscar Horarios</h1>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('paciente.citas.buscar') }}" class="row g-3">
                <div class="col-md-5">
                    <label for="input-especialidad" class="form-label fw-semibold">Especialidad</label>
                    <select name="id_especialidad" id="input-especialidad" class="form-select" required>
                        <option value="">Seleccione...</option>
                        @foreach (\App\Models\Especialidad::activas()->get() as $esp)
                            <option value="{{ $esp->id_especialidad }}">{{ $esp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="input-sede" class="form-label fw-semibold">Sede <span
                            class="text-muted">(opcional)</span></label>
                    <select name="id_sede" id="input-sede" class="form-select">
                        <option value="">Cualquier sede</option>
                        @foreach (\App\Models\Sede::activas()->get() as $sede)
                            <option value="{{ $sede->id_sede }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (isset($slots))
        <div class="row">
            @forelse($slots as $slot)
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge"
                                    style="background: var(--mediq-azul-pale); color: var(--mediq-azul-oscuro);">
                                    {{ $slot->hora_inicio }} – {{ $slot->hora_fin }}
                                </span>
                            </div>
                            <h3 class="h6 fw-semibold mb-1">{{ $slot->medico->nombres_apellidos }}</h3>
                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-location-dot"></i> {{ $slot->sede->nombre }}
                            </p>

                            <form method="POST" action="{{ route('paciente.citas.reservar') }}" class="mt-auto">
                                @csrf
                                <input type="hidden" name="id_agenda" value="{{ $slot->id_agenda }}">
                                <textarea name="motivo_consulta" class="form-control form-control-sm mb-2" rows="2"
                                    placeholder="Motivo de consulta (opcional)"></textarea>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa-solid fa-check"></i> Reservar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <p>No hay horarios disponibles para este filtro.</p>
                </div>
            @endforelse
        </div>
    @endif
@endsection
