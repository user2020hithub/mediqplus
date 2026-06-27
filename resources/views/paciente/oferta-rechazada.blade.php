@extends('layouts.app')

@section('titulo', 'Oferta rechazada')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-md-5">
                    <i class="fa-solid fa-circle-info fa-2x mb-3 text-mediq-azul-medio"></i>
                    <h1 class="h4 fw-bold mb-2 text-mediq-azul-oscuro">
                        Oferta rechazada
                    </h1>
                    <p class="text-muted mb-4">
                        Gracias por avisarnos. Estamos ofreciendo este cupo al siguiente
                        candidato en la lista de espera. Seguirá recibiendo notificaciones
                        cuando haya un nuevo cupo disponible que coincida con su solicitud.
                    </p>
                    <a href="{{ route('auth.login') }}" class="btn btn-primary">
                        Ir al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
