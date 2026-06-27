@extends('layouts.app')

@section('titulo', 'Resumen de Triaje')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="{{ route('medico.dashboard') }}" class="text-decoration-none mb-3 d-inline-block">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-1 text-mediq-azul-oscuro">Resumen de Triaje</h1>
                    <p class="text-muted mb-4">
                        {{ $cita->codigo_cita }} — {{ $cita->paciente->nombres_apellidos }}
                    </p>

                    @if ($triaje)
                        <dl class="row">
                            <dt class="col-sm-4 fw-semibold">Motivo de consulta</dt>
                            <dd class="col-sm-8">{{ $triaje->motivo_consulta }}</dd>

                            <dt class="col-sm-4 fw-semibold">Síntomas</dt>
                            <dd class="col-sm-8">{{ implode(', ', $triaje->sintomas ?? []) }}</dd>

                            <dt class="col-sm-4 fw-semibold">Intensidad</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-intensidad">
                                    {{ $triaje->intensidad }} / 10
                                </span>
                            </dd>

                            <dt class="col-sm-4 fw-semibold">¿Alergias?</dt>
                            <dd class="col-sm-8">{{ $triaje->tiene_alergias ? 'Sí' : 'No' }}</dd>

                            @if ($triaje->tiene_alergias)
                                <dt class="col-sm-4 fw-semibold">Detalle</dt>
                                <dd class="col-sm-8">{{ $triaje->detalle_alergias }}</dd>
                            @endif

                            @if ($triaje->medicamentos_actuales)
                                <dt class="col-sm-4 fw-semibold">Medicamentos</dt>
                                <dd class="col-sm-8">{{ $triaje->medicamentos_actuales }}</dd>
                            @endif
                        </dl>
                    @else
                        <p class="text-muted">Este paciente todavía no completó el triaje.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
