<?php

use Illuminate\Support\Facades\Route;

/*
Route::get('/', function () {
    return view('welcome');
});
*/

/*
|------------------------------------------------------------
| MEDIQ+ — Rutas Base
| Clínica TuSalud — Integrador II Sistemas — UTP
|------------------------------------------------------------
*/

// ── Ruta raíz (redirige al login) ────────────────────────
Route::get("/", fn() => redirect()->route("login"))->name("home");

// ── Autenticación (CU-01, CU-02, CU-11) ──────────────────
Route::prefix("auth")->name("auth.")->group(function () {
    Route::get("/registro",  [AuthController::class, "showRegistro"])->name("registro");
    Route::post("/registro", [AuthController::class, "registro"]);
    Route::get("/login",     [AuthController::class, "showLogin"])->name("login");
    Route::post("/login",    [AuthController::class, "login"]);
    Route::post("/logout",   [AuthController::class, "logout"])->name("logout");
});

// ── Paciente (middleware: auth + rol paciente) ─────────────
Route::prefix("paciente")->name("paciente.")
    ->middleware(["auth", "role:paciente"])
    ->group(function () {
        Route::get("/dashboard", fn() => view("paciente.dashboard"))->name("dashboard");
        // CU-03, CU-04, CU-07, CU-10, CU-13 — se definen en Fase 4+
    });

// ── Médico (middleware: auth + rol medico + 2FA) ───────────
Route::prefix("medico")->name("medico.")
    ->middleware(["auth", "role:medico", "2fa"])
    ->group(function () {
        Route::get("/dashboard", fn() => view("medico.dashboard"))->name("dashboard");
        // CU-04, CU-06 — se definen en Fase 4+
    });

// ── Admin (middleware: auth + rol admin + 2FA) ─────────────
Route::prefix("admin")->name("admin.")
    ->middleware(["auth", "role:admin", "2fa"])
    ->group(function () {
        Route::get("/dashboard", fn() => view("admin.dashboard"))->name("dashboard");
        // CU-05, CU-06, CU-12 — se definen en Fase 4+
    });
