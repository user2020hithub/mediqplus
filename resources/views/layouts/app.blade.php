<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'MEDIQ+') | Clínica TuSalud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-mediq-azul-oscuro">
        <div class="container">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2"
                href="{{ auth('web')->user()?->rol ? route(auth('web')->user()->rol . '.dashboard') : route('auth.login') }}">
                <i class="fa-solid fa-heart-pulse"></i> MEDIQ+
            </a>

            @auth('web')
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white-50 small d-none d-md-inline">
                        {{ auth('web')->user()->correo_electronico }}
                    </span>
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            <i class="fa-solid fa-right-from-bracket"></i> Salir
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="container py-4 py-md-5">
        @if (session('exito'))
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-check"></i> {{ session('exito') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
            </div>
        @endif

        @yield('contenido')
    </main>

    <footer class="text-center text-muted small py-4">
        Clínica TuSalud — Sistema MEDIQ+ · Proyecto académico UTP
    </footer>
</body>

</html>
