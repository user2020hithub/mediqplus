@extends('layouts.app')

@section('titulo', 'Registrar Médico')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-7">
            <a href="{{ route('admin.medicos.index') }}" class="text-decoration-none mb-3 d-inline-block">
                <i class="fa-solid fa-arrow-left"></i> Volver al listado
            </a>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-4" style="color: var(--mediq-azul-oscuro);">Registrar Médico</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.medicos.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="input-nombres" class="form-label fw-semibold">Nombres y apellidos</label>
                            <input type="text" name="nombres_apellidos" id="input-nombres" class="form-control"
                                value="{{ old('nombres_apellidos') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="input-dni" class="form-label fw-semibold">DNI</label>
                                <input type="text" name="dni" id="input-dni" class="form-control" maxlength="8"
                                    value="{{ old('dni') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="input-colegiatura" class="form-label fw-semibold">Colegiatura</label>
                                <input type="text" name="colegiatura" id="input-colegiatura" class="form-control"
                                    value="{{ old('colegiatura') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="input-correo" class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email" name="correo_electronico" id="input-correo" class="form-control"
                                value="{{ old('correo_electronico') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="input-telefono" class="form-label fw-semibold">Teléfono <span
                                    class="text-muted">(opcional)</span></label>
                            <input type="text" name="telefono" id="input-telefono" class="form-control" maxlength="9"
                                value="{{ old('telefono') }}">
                        </div>

                        <div class="mb-3">
                            <label for="input-especialidad" class="form-label fw-semibold">Especialidad</label>
                            <select name="id_especialidad" id="input-especialidad" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach ($especialidades as $esp)
                                    <option value="{{ $esp->id_especialidad }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Sedes asignadas</label>
                            <div class="p-3 rounded" style="background: #F8FAFB;">
                                @foreach ($sedes as $sede)
                                    <div class="form-check">
                                        <input type="checkbox" name="sedes[]" value="{{ $sede->id_sede }}"
                                            class="form-check-input" id="sede{{ $sede->id_sede }}">
                                        <label class="form-check-label" for="sede{{ $sede->id_sede }}">
                                            {{ $sede->nombre }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            Registrar Médico
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
