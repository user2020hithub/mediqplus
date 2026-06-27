@extends('layouts.app')

@section('titulo', 'Triaje no disponible')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-md-5">
                    <i class="fa-solid fa-clock-rotate-left fa-2x mb-3 text-mediq-ambar"></i>
                    <h1 class="h4 fw-bold mb-2 text-mediq-azul-oscuro">
                        Triaje no disponible todavía
                    </h1>
                    <p class="text-muted mb-4">
                        El formulario de triaje solo está disponible entre 24 horas y 1 hora
                        antes de su cita. Por favor, vuelva a intentarlo más cerca de la fecha programada.
                    </p>
                    <a href="{{ route('paciente.dashboard') }}" class="btn btn-primary">
                        <i class="fa-solid fa-arrow-left"></i> Volver a mi panel
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
