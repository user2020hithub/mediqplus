@extends('layouts.app')

@section('titulo', 'Crear Cuenta')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 fw-bold mb-1 text-mediq-azul-oscuro">Crear Cuenta</h1>
                    <p class="text-muted mb-4">Solo toma un par de minutos</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.registro') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="input-tipo-documento" class="form-label fw-semibold">Tipo doc.</label>
                                <select name="tipo_documento" id="input-tipo-documento" class="form-select" required>

                                    <option value="DNI">DNI</option>
                                    <option value="CE">CE</option>
                                    <option value="CPP">CPP</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="input-numero-documento" class="form-label fw-semibold">Número de
                                    documento</label>
                                <input type="text" name="numero_documento" id="input-numero-documento"
                                    class="form-control" value="{{ old('numero_documento') }}" required>

                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="input-nombres" class="form-label fw-semibold">Nombres y apellidos</label>
                            <input type="text" name="nombres_apellidos" id="input-nombres" class="form-control"
                                value="{{ old('nombres_apellidos') }}" required>

                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="input-fecha-nacimiento" class="form-label fw-semibold">Fecha de
                                    nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="input-fecha-nacimiento"
                                    class="form-control" value="{{ old('fecha_nacimiento') }}" required>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="input-sexo" class="form-label fw-semibold">Sexo</label>
                                <select name="sexo" id="input-sexo" class="form-select" required>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="input-correo" class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email" name="correo_electronico" id="input-correo" class="form-control"
                                value="{{ old('correo_electronico') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="input-telefono" class="form-label fw-semibold">Teléfono móvil <span
                                    class="text-muted">(opcional)</span></label>
                            <input type="text" name="telefono_movil" id="input-telefono" class="form-control"
                                value="{{ old('telefono_movil') }}" maxlength="9">

                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="input-password" class="form-label fw-semibold">Contraseña</label>
                                <input type="password" name="password" id="input-password" class="form-control" required>
                                <small class="text-muted">Mín. 8 caracteres, 1 mayúscula, 1 número, 1 especial.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="input-password-confirmation" class="form-label fw-semibold">Confirmar
                                    contraseña</label>
                                <input type="password" name="password_confirmation" id="input-password-confirmation"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="form-check mb-4 p-3 rounded bg-mediq-gris-claro">
                            <input type="checkbox" name="acepta_privacidad" class="form-check-input" id="privacidad"
                                required>
                            <label class="form-check-label" for="privacidad">
                                Acepto la <strong>Política de Privacidad</strong> (Ley N° 29733)
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">Registrarme</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        ¿Ya tiene cuenta? <a href="{{ route('auth.login') }}" class="fw-semibold">Inicie sesión aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
