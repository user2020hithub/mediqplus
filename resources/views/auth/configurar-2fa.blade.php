@extends('layouts.app')

@section('titulo', 'Configurar Autenticación')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-md-5">
                    <i class="fa-solid fa-shield-halved fa-2x mb-3 text-mediq-azul-medio"></i>
                    <h1 class="h4 fw-bold mb-2 text-mediq-azul-oscuro">
                        Configurar Verificación en Dos Pasos
                    </h1>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <p class="text-muted mb-4">
                        Escanee este código QR con Google Authenticator, Authy, o una app similar.
                    </p>

                    <div class="d-flex justify-content-center mb-3">
                        <img src="https://chart.googleapis.com/chart?cht=qr&chs=220x220&chl={{ urlencode($qrUrl) }}"
                            alt="Código QR para verificación en dos pasos" class="rounded border p-2"
                            style="background: white;">
                    </div>

                    <div class="p-3 rounded mb-4" style="background: #F8FAFB;">
                        <p class="small text-muted mb-1">¿No puede escanear? Use este código manual:</p>
                        <code class="fs-6 fw-semibold text-mediq-azul-oscuro">
                            {{ $secretoManual }}
                        </code>
                    </div>

                    <form method="POST" action="{{ route('2fa.confirmar') }}">
                        @csrf
                        <div class="mb-3 text-start">
                            <label class="form-label fw-semibold">
                                Ingrese el código de 6 dígitos para confirmar
                            </label>
                            <input type="text" name="totp" class="form-control form-control-lg text-center"
                                maxlength="6" placeholder="123456" autofocus required
                                style="letter-spacing: 0.3em; font-weight: 600;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            Confirmar y Activar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
