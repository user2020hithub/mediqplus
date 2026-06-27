<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TotpSetupController;
use App\Http\Controllers\Paciente\DashboardController as PacienteDashboard;
use App\Http\Controllers\Paciente\CitaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MedicoController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Paciente\OfertaReasignacionController;
use App\Http\Controllers\Paciente\TriajeController as PacienteTriaje;
use App\Http\Controllers\Medico\TriajeController as MedicoTriaje;
use App\Http\Controllers\Paciente\ListaEsperaController;
use App\Http\Controllers\Paciente\ReprogramacionController;
use App\Http\Controllers\Admin\ContingenciaController;


Route::get('/', function () {
    $usuario = auth('web')->user();

    if (! $usuario) {
        return redirect()->route('auth.login');
    }

    $dashboard = match ($usuario->rol) {
        'paciente' => 'paciente.dashboard',
        'medico'   => 'medico.dashboard',
        'admin'    => 'admin.dashboard',
        default    => 'auth.login',
    };

    return redirect()->route($dashboard);
})->name('home');

// ── Autenticación (CU-01, CU-02) ───────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/registro',  [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registro']);
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')->name('logout');
});

// ── Configuración de 2FA (CU-11) — requiere estar autenticado ──
Route::middleware('auth:sanctum')->prefix('2fa')->name('2fa.')->group(function () {
    Route::get('/configurar',  [TotpSetupController::class, 'mostrarConfiguracion'])->name('configurar');
    Route::post('/confirmar',  [TotpSetupController::class, 'confirmarConfiguracion'])->name('confirmar');
});

// ── Paciente: ahora con middleware real ─────────────────────
Route::prefix('paciente')->name('paciente.')
    ->middleware(['auth:sanctum', 'role:paciente'])
    ->group(function () {
        Route::get('/dashboard', [PacienteDashboard::class, 'index'])->name('dashboard');
        Route::get('/citas/buscar', [CitaController::class, 'buscarDisponibilidad'])->name('citas.buscar');
        Route::post('/citas/reservar', [CitaController::class, 'reservar'])->name('citas.reservar');
    });

// ── Médico: middleware real + verificación de 2FA habilitado ── también puede gestionar SU PROPIA agenda (CU-06) ──
Route::prefix('medico')->name('medico.')
    ->middleware(['auth:sanctum', 'role:medico'])
    ->group(function () {
        Route::get('/dashboard', fn() => view('medico.dashboard'))->name('dashboard');
        Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
        Route::get('/lista-espera/crear', [ListaEsperaController::class, 'mostrarFormulario'])
            ->name('lista-espera.crear');
        Route::post('/lista-espera', [ListaEsperaController::class, 'suscribir'])
            ->name('lista-espera.store');
        Route::get('/lista-espera/mias', [ListaEsperaController::class, 'misSuscripciones'])
            ->name('lista-espera.mias');
        Route::patch('/lista-espera/{listaEspera}/cancelar', [ListaEsperaController::class, 'cancelar'])
            ->name('lista-espera.cancelar');
    });

// ── Reprogramación (Opción B) — SIN sesión, seguridad vía token ──
Route::get('/reprogramar/{token}', [ReprogramacionController::class, 'mostrarOpciones'])
    ->name('paciente.citas.reprogramar');
Route::post('/reprogramar/{token}', [ReprogramacionController::class, 'confirmar'])
    ->name('paciente.citas.reprogramar.confirmar');


// ── Admin: middleware real + verificación de 2FA habilitado ───
Route::prefix('admin')->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        Route::get('/medicos', [MedicoController::class, 'index'])->name('medicos.index');
        Route::get('/medicos/crear', [MedicoController::class, 'create'])->name('medicos.create');
        Route::post('/medicos', [MedicoController::class, 'store'])->name('medicos.store');
        Route::patch('/medicos/{medico}/desactivar', [MedicoController::class, 'desactivar'])
            ->name('medicos.desactivar');

        Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
        Route::patch('/agenda/{agenda}/bloquear', [AgendaController::class, 'bloquear'])
            ->name('agenda.bloquear');

        Route::get('/contingencia', [ContingenciaController::class, 'mostrarFormulario'])
            ->name('contingencia.crear');
        Route::post('/contingencia', [ContingenciaController::class, 'ejecutar'])
            ->name('contingencia.ejecutar');
    });

// Estas rutas NO llevan middleware 'auth:sanctum' porque el paciente
// hace clic en un enlace de correo, posiblemente sin sesión activa.
// La seguridad la da el TOKEN aleatorio de 64 caracteres en la URL,
// no la sesión — exactamente como pide el paso 7 del SRS.
Route::get(
    '/oferta-reasignacion/{token}/aceptar',
    [OfertaReasignacionController::class, 'aceptar']
)->name('oferta.aceptar');
Route::get(
    '/oferta-reasignacion/{token}/rechazar',
    [OfertaReasignacionController::class, 'rechazar']
)->name('oferta.rechazar');

Route::prefix('paciente')->name('paciente.')
    ->middleware(['auth:sanctum', 'role:paciente'])
    ->group(function () {
        Route::patch('/citas/{cita}/cancelar', [CitaController::class, 'cancelar'])
            ->name('citas.cancelar');
        Route::get('/citas/{cita}/triaje', [PacienteTriaje::class, 'mostrarFormulario'])
            ->name('triaje.formulario');
        Route::post('/citas/{cita}/triaje', [PacienteTriaje::class, 'guardar'])
            ->name('triaje.guardar');
    });

Route::prefix('medico')->name('medico.')
    ->middleware(['auth:sanctum', 'role:medico'])
    ->group(function () {
        Route::get('/citas/{cita}/triaje', [MedicoTriaje::class, 'verResumen'])
            ->name('triaje.resumen');
    });
