@extends('layouts.app')

@section('contenido')
<div class="container mt-5" style="max-width: 420px;">
    <h2 class="mb-4">Iniciar Sesión — MEDIQ+</h2>

    {{-- Mensajes de error o información --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <form method="POST" action="{{ route('auth.login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="identificador" class="form-control"
                   value="{{ old('identificador') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        {{-- Este campo SOLO aparece en el segundo submit (rol admin/medico) --}}
        @if(session('mostrar_totp'))
            <div class="mb-3">
                <label class="form-label">Código de verificación (app autenticadora)</label>
                <input type="text" name="totp" class="form-control" maxlength="6"
                       placeholder="123456" autofocus required>
            </div>
        @endif

        <div class="form-check mb-3">
            <input type="checkbox" name="recordar" class="form-check-input" id="recordar">
            <label class="form-check-label" for="recordar">Recordar sesión (7 días)</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
    </form>

    <p class="mt-3 text-center">
        ¿No tiene cuenta? <a href="{{ route('auth.registro') }}">Regístrese aquí</a>
    </p>
</div>
@endsection
