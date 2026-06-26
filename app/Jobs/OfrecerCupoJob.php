<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Models\AuditoriaReasignacion;
use App\Mail\OfertaReasignacionMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

// Job de ALTA prioridad (paso 2 del SRS de CU-09: "Alta" para reasignación).
// Maneja un Candidato a la vez; si falla o expira, se vuelve a despachar
// a sí mismo (con $intentoActual + 1) para probar al siguiente candidato.
class OfrecerCupoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // el reintento de candidatos se maneja manualmente, no por Laravel

    public function __construct(
        public Cita $citaOrigen,
        public array $candidatosOrdenados, // Top 5 con sus scores ya calculados
        public int $intentoActual // 0-indexed: 0 = primer candidato
    ) {
        $this->onQueue('alta'); // cola dedicada de alta prioridad
    }

    public function handle(): void
    {
        // ── Flujo principal paso 11 del SRS: si se acabaron los 5
        // intentos, hacer el fallback de negocio (liberar el cupo) ──
        if ($this->intentoActual >= count($this->candidatosOrdenados)) {
            $this->citaOrigen->agenda->update(['estado' => 'Disponible']);

            AuditoriaReasignacion::create([
                'id_cita_origen' => $this->citaOrigen->id_cita,
                'id_candidato' => null,
                'intento_numero' => $this->intentoActual,
                'resultado' => 'Sin_Candidatos',
            ]);
            return;
        }

        $candidatoActual = $this->candidatosOrdenados[$this->intentoActual]['candidato'];
        $scoreActual = $this->candidatosOrdenados[$this->intentoActual]['score'];

        // ── Paso 7 del SRS: generar token único con expiración 15 min ──
        // Se usa Cache (no una columna en BD) porque es un dato TRANSITORIO
        // — no tiene sentido persistirlo permanentemente en citas o agenda.
        $token = Str::random(64);
        Cache::put("oferta_reasignacion:{$token}", [
            'id_cita_origen' => $this->citaOrigen->id_cita,
            'id_candidato' => $candidatoActual->id_lista,
            'intento_actual' => $this->intentoActual,
            'candidatos_restantes' => $this->candidatosOrdenados,
        ], now()->addMinutes(15));

        // Auditoría del intento de oferta (paso 12 del SRS)
        AuditoriaReasignacion::create([
            'id_cita_origen' => $this->citaOrigen->id_cita,
            'id_candidato' => $candidatoActual->id_paciente,
            'score_calculado' => $scoreActual,
            'intento_numero' => $this->intentoActual + 1,
            'resultado' => 'Oferta_Enviada',
        ]);

        // ── Despachar el correo de oferta (CU-09) ────────────────────
        Mail::to($candidatoActual->paciente->usuario->correo_electronico)
            ->queue(new OfertaReasignacionMail($this->citaOrigen, $candidatoActual, $token));

        // ── Paso 8 del SRS: Delayed Job de 15 minutos ────────────────
        // Si el paciente no responde en 15 min, este mismo Job se vuelve
        // a ejecutar pero con el SIGUIENTE candidato — así se logra el
        // ciclo de hasta 5 intentos sin necesidad de un Job separado.
        self::dispatch($this->citaOrigen, $this->candidatosOrdenados, $this->intentoActual + 1)
            ->delay(now()->addMinutes(15));
    }
}
