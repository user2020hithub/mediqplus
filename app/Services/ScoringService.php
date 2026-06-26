<?php

namespace App\Services;

use App\Models\ListaEspera;
use App\Models\Cita;

// Aísla EXCLUSIVAMENTE el cálculo matemático del score. Esto permite
// probarlo unitariamente en la Fase 8 sin necesidad de simular todo
// el flujo de cancelación + cola + listener.
class ScoringService
{
    // Constantes de la fórmula confirmada (sección "Relaciones entre
    // entradas y salidas" de RF-08 en el SRS):
    private const PESO_MATCH = 0.4;
    private const PESO_PROXIMIDAD = 0.3;
    private const PESO_ASISTENCIA = 0.2;
    private const PESO_ANTIGUEDAD = 0.1;

    private const TOPE_DELTA_HORAS = 120;       // 5 días, tope del componente "proximidad"
    private const TOPE_DIAS_ULTIMA_CITA = 365;   // 1 año, tope del componente "antiguedad"

    /**
     * Calcula el score_total de UN candidato de lista de espera,
     * exactamente con la fórmula confirmada:
     *
     *   score = 0.4*I_match + 0.3*proximidad + 0.2*asistencia + 0.1*antiguedad
     */
    public function calcularScore(ListaEspera $candidato): float
    {
        // ── I_match (40%) ─────────────────────────────────────────
        // Siempre es 1 porque el query que arma la lista de candidatos
        // (ver ReasignacionListener) ya filtró por especialidad_id Y
        // sede_id coincidentes con el cupo liberado — no hay forma de
        // que un candidato llegue aquí sin cumplir el match.
        $iMatch = 1;

        // ── proximidad (30%) ──────────────────────────────────────
        // Mide qué tan RECIENTE es la solicitud del candidato en la
        // lista de espera. Entre menos horas lleve esperando, MÁS alto
        // el score (esto premia a quien se suscribió hace poco, lo cual
        // tiene sentido si se interpreta como "todavía está interesado",
        // a diferencia de alguien que se suscribió hace mucho y quizás
        // ya resolvió su necesidad por otro lado).
        $deltaHoras = $candidato->fecha_creacion->diffInHours(now());
        $proximidad = 1 - min($deltaHoras / self::TOPE_DELTA_HORAS, 1);

        // ── asistencia (20%) ──────────────────────────────────────
        // Tasa de asistencia real del paciente (NO es predicción con IA,
        // es un conteo histórico simple vía SQL — el SRS solo prohíbe
        // modelos de Machine Learning para predecir el futuro, no
        // calcular estadísticas sobre el pasado).
        $totalCitas = Cita::where('id_paciente', $candidato->id_paciente)
            ->whereIn('estado', ['Atendida', 'No_Show'])
            ->count();

        if ($totalCitas === 0) {
            // Paciente sin historial: se le da el beneficio de la duda (score = 1)
            $asistencia = 1;
        } else {
            $noShows = Cita::where('id_paciente', $candidato->id_paciente)
                ->where('estado', 'No_Show')
                ->count();
            $asistencia = 1 - ($noShows / $totalCitas);
        }

        // ── antiguedad (10%) ───────────────────────────────────────
        // Mide hace cuánto que el paciente NO ha tenido una cita atendida.
        // Entre más tiempo sin atenderse, MÁS alto el score — esto
        // fomenta la retención de pacientes que lo necesitan, en línea
        // con el objetivo declarado del sistema (reducir el no-show y
        // mejorar la ocupación real de la agenda).
        $ultimaCita = Cita::where('id_paciente', $candidato->id_paciente)
            ->where('estado', 'Atendida')
            ->orderByDesc('fecha_cita')
            ->first();

        if (!$ultimaCita) {
            // Nunca ha sido atendido: se le da el máximo de este componente
            $antiguedad = 1;
        } else {
            $diasDesdeUltimaCita = $ultimaCita->fecha_cita->diffInDays(now());
            $antiguedad = min($diasDesdeUltimaCita / self::TOPE_DIAS_ULTIMA_CITA, 1);
        }

        // ── Score final ponderado ─────────────────────────────────
        $scoreTotal = (self::PESO_MATCH * $iMatch)
            + (self::PESO_PROXIMIDAD * $proximidad)
            + (self::PESO_ASISTENCIA * $asistencia)
            + (self::PESO_ANTIGUEDAD * $antiguedad);

        // Se redondea a 2 decimales porque la columna score_calculado
        // de auditoria_reasignacion es DECIMAL(5,2) (definida en la Fase 2).
        return round($scoreTotal, 2);
    }
}
