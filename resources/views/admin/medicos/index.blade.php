@extends('layouts.app')

@section('titulo', 'Gestión de Médicos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0" style="color: var(--mediq-azul-oscuro);">Gestión de Médicos</h1>
    <a href="{{ route('admin.medicos.create') }}" class="btn btn-success">
        <i class="fa-solid fa-user-plus"></i> Registrar Médico
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="text-muted small">
                        <th>Nombre</th><th>Especialidad</th><th>Sedes</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicos as $medico)
                        <tr>
                            <td class="fw-semibold">
                                <i class="fa-solid fa-user-doctor text-muted me-1"></i>
                                {{ $medico->nombres_apellidos }}
                            </td>
                            <td>{{ $medico->especialidad->nombre ?? '—' }}</td>
                            <td>{{ $medico->sedes->pluck('nombre')->join(', ') }}</td>
                            <td>
                                @if($medico->estado === 'Activo')
                                    <span class="badge badge-estado-confirmada">
                                        <i class="fa-solid fa-circle-check"></i> Activo
                                    </span>
                                @else
                                    <span class="badge badge-estado-cancelada">
                                        <i class="fa-solid fa-circle-xmark"></i> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($medico->estado === 'Activo')
                                    <form method="POST" action="{{ route('admin.medicos.desactivar', $medico->id_medico) }}"
                                          onsubmit="return confirm('¿Confirma desactivar a este médico?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-ban"></i> Desactivar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay médicos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $medicos->links() }}
    </div>
</div>
@endsection
