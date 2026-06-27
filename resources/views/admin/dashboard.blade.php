@extends('layouts.app')

@section('titulo', 'Panel de Administración')

@section('contenido')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0 text-mediq-azul-oscuro">Panel de Administración</h1>
        <a href="{{ route('admin.medicos.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-doctor"></i> Gestionar Médicos
        </a>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="fa-solid fa-calendar-days fa-lg mb-2 text-mediq-azul-medio"></i>
                    <h2 class="h3 fw-bold mb-0">{{ $kpis->total_citas ?? 0 }}</h2>
                    <p class="text-muted small mb-0">Total citas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="fa-solid fa-circle-check fa-lg mb-2 text-mediq-verde-salud"></i>
                    <h2 class="h3 fw-bold mb-0">{{ $kpis->confirmadas ?? 0 }}</h2>
                    <p class="text-muted small mb-0">Confirmadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="fa-solid fa-circle-xmark fa-lg mb-2 text-mediq-rojo"></i>
                    <h2 class="h3 fw-bold mb-0">{{ $kpis->canceladas ?? 0 }}</h2>
                    <p class="text-muted small mb-0">Canceladas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <i class="fa-solid fa-percent fa-lg mb-2 text-mediq-azul-oscuro"></i>
                    <h2 class="h3 fw-bold mb-0">{{ $tasaConfirmacion ?? 0 }}%</h2>
                    <p class="text-muted small mb-0">Tasa confirmación</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Citas en el rango seleccionado</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Código</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Sede</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citas ?? [] as $cita)
                            <tr>
                                <td class="fw-semibold">{{ $cita->codigo_cita }}</td>
                                <td>{{ $cita->paciente->nombres_apellidos ?? '—' }}</td>
                                <td>{{ $cita->medico->nombres_apellidos ?? '—' }}</td>
                                <td>{{ $cita->sede->nombre ?? '—' }}</td>
                                <td>{{ $cita->fecha_cita->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-estado-{{ strtolower($cita->estado) }}">
                                        {{ str_replace('_', ' ', $cita->estado) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Sin citas en este rango.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (method_exists($citas ?? null, 'links'))
                {{ $citas->links() }}
            @endif
        </div>
    </div>
@endsection
