@extends('layouts.app')

@section('titulo', 'Iniciar Sesión')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-heart-pulse fa-2x mb-2 text-mediq-azul-medio"></i>
                        <h1 class="h3 fw-bold text-mediq-azul-oscuro">Bienvenido a MEDIQ+</h1>
                        <p class="text-muted">Ingrese sus datos para continuar</p>
                    </div>

                    <form method="POST" action="{{ route('auth.login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="input-identificador" class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email" name="identificador" id="input-identificador"
                                class="form-control form-control-lg" value="{{ old('identificador') }}" required autofocus>

                        </div>

                        <div class="mb-3">
                            <label for="input-password" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" name="password" id="input-password" class="form-control form-control-lg"
                                required>

                        </div>

                        @if (session('mostrar_totp'))
                            <div class="mb-3 p-3 rounded" style="background: var(--mediq-azul-pale);">
                                <label for="input-totp" class="form-label fw-semibold">
                                    <i class="fa-solid fa-shield-halved"></i> Código de verificación
                                </label>
                                <input type="text" name="totp" id="input-totp"
                                    class="form-control form-control-lg text-center" maxlength="6" placeholder="123456"
                                    autofocus required style="letter-spacing: 0.3em; font-weight: 600;">
                                <small class="text-muted">Ingrese el código de 6 dígitos de su app autenticadora.</small>
                            </div>
                        @endif

                        <div class="form-check mb-4">
                            <input type="checkbox" name="recordar" class="form-check-input" id="recordar">
                            <label class="form-check-label" for="recordar">Recordar mi sesión por 7 días</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            Iniciar Sesión
                        </button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        ¿No tiene cuenta? <a href="{{ route('auth.registro') }}" class="fw-semibold">Regístrese aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
