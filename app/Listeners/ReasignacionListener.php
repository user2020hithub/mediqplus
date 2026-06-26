<?php

namespace App\Listeners;

use App\Events\CitaCancelada;
use App\Models\Agenda;
use App\Models\ListaEspera;
use App\Models\AuditoriaReasignacion;
use App\Services\ScoringService;
use App\Jobs\OfrecerCupoJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ShouldQueue convierte este Listener en asíncrono — Laravel lo procesa
// en segundo plano vía el Queue Worker, no bloqueando la respuesta HTTP
// de la cancelación (cumple el RNF-REND-02: ≤ 5 min, sin afectar la
// experiencia del paciente que cancela).
class ReasignacionListener implements ShouldQueue
{
    public function __construct(private ScoringService $scoringService) {}

    public function handle(CitaCancelada $event): void
    {
        $cita = $event->cita;
        $agenda = $cita->agenda;

        try {
            DB::beginTransaction();

            // ── Paso 2 del SRS: bloqueo transitorio del slot ─────────
            // Esto evita que un paciente público reserve el slot mientras
            // el algoritmo de reasignación todavía está evaluando candidatos.
            $agenda->update(['estado' => 'En_Proceso_Reasignacion']);

            // ── Paso 3 del SRS: consultar candidatos elegibles ───────
            $candidatos = ListaEspera::where(
                'id_especialidad',
                $cita->medico->id_especialidad
            )
                ->where('id_sede', $cita->id_sede)
                ->where('estado', 'Activa')
                ->get();

            // ── Flujo alternativo 3a del SRS: sin candidatos ─────────
            if ($candidatos->isEmpty()) {
                $agenda->update(['estado' => 'Disponible']);

                AuditoriaReasignacion::create([
                    'id_cita_origen' => $cita->id_cita,
                    'id_candidato' => null,
                    'intento_numero' => 1,
                    'resultado' => 'Sin_Candidatos',
                ]);

                DB::commit();
                return;
            }

            // ── Pasos 4 y 5 del SRS: calcular scores y ordenar ───────
            $candidatosConScore = $candidatos->map(function ($candidato) {
                return [
                    'candidato' => $candidato,
                    'score' => $this->scoringService->calcularScore($candidato),
                ];
            })->sortByDesc('score')->values();

            // Top 5 (paso 5 del SRS)
            $top5 = $candidatosConScore->take(5);

            DB::commit();

            // ── Pasos 7-8 del SRS: ofrecer al Candidato #1 ───────────
            // El despacho del primer intento se hace FUERA de la
            // transacción anterior (que ya cerró), y el propio Job
            // se encarga de manejar el ciclo de 5 intentos con
            // Delayed Jobs de 15 minutos cada uno.
            OfrecerCupoJob::dispatch($cita, $top5->toArray(), intentoActual: 0);
        } catch (\Exception $e) {
            DB::rollBack();

            // ── Flujo alternativo 4a del SRS: timeout de BD ──────────
            // Se libera el cupo inmediatamente para no perder la venta,
            // y se registra una alerta crítica para revisión del equipo.
            $agenda->update(['estado' => 'Disponible']);
            Log::critical('[CU-08] Fallo crítico en motor de reasignación', [
                'id_cita' => $cita->id_cita,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
