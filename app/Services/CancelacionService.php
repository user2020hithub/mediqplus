<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Auditoria;
use App\Events\CitaCancelada;
use Illuminate\Support\Facades\DB;

// Aísla la lógica de negocio de la cancelación, igual que se hizo
// con CitaService (reserva) en la Fase 4 — mismo patrón de diseño.
class CancelacionService
{
    /**
     * Cancela una cita validando que pertenezca al paciente y que esté
     * en un estado cancelable, dentro de una transacción ACID con
     * bloqueo pesimista (paso 4 del SRS de CU-07).
     */
    public function cancelar(Cita $cita, int $idPacienteSolicitante, string $motivo): Cita
    {
        return DB::transaction(function () use ($cita, $idPacienteSolicitante, $motivo) {

            // ── Paso 4 del SRS: bloqueo pesimista para evitar condiciones
            // de carrera (ej. que el motor de reasignación y el paciente
            // intenten tocar la misma cita al mismo tiempo).
            $citaBloqueada = Cita::where('id_cita', $cita->id_cita)
                ->lockForUpdate()
                ->first();

            // ── Paso 5 del SRS: prevención de IDOR (flujo alternativo 5b) ──
            // Esta validación es la que impide que alguien cancele una cita
            // ajena manipulando el id_cita en la petición.
            if ($citaBloqueada->id_paciente !== $idPacienteSolicitante) {
                throw new \App\Exceptions\AccesoNoAutorizadoException('No tiene permisos para cancelar esta cita.');
            }

            // ── Paso 5 del SRS: solo se puede cancelar si está en un
            // estado activo (flujo alternativo 5a) ──
            if (!in_array($citaBloqueada->estado, ['Pendiente_Confirmacion', 'Confirmada'])) {
                throw new \App\Exceptions\ReservaException(
                    'Esta cita ya fue atendida o cancelada previamente y no puede ser modificada.'
                );
            }

            // ── Paso 6 del SRS: cálculo del flag de cancelación tardía ──
            // Si quedan menos de 24h para la cita, se marca como tardía
            // (esto alimenta estadísticas de no-show, sin penalizar nada
            // automáticamente en esta fase — solo se registra el dato).
            $fechaHoraCita = \Carbon\Carbon::parse(
                $citaBloqueada->fecha_cita->format('Y-m-d') . ' ' . $citaBloqueada->hora_inicio
            );
            $esCancelacionTardia = now()->diffInHours($fechaHoraCita, false) < 24;

            // ── Pasos 7 y 8 del SRS: actualizar cita Y liberar el slot,
            // ambos dentro de la misma transacción (atomicidad) ──
            $citaBloqueada->update([
                'estado' => 'Cancelada',
                'motivo_cancelacion' => $motivo,
                'fecha_cancelacion' => now(),
                'es_cancelacion_tardia' => $esCancelacionTardia,
                'usuario_cancelacion' => auth()->id(),
            ]);

            $citaBloqueada->agenda->update(['estado' => 'Disponible']);

            // ── Paso 11 del SRS: auditoría con todos los metadatos exigidos ──
            Auditoria::registrar(auth()->id(), 'CANCELAR_CITA', 'citas', $citaBloqueada->id_cita, [
                'motivo' => $motivo,
                'es_cancelacion_tardia' => $esCancelacionTardia,
            ]);

            // ── Paso 10 del SRS: disparar el evento DESPUÉS del commit ──
            // Laravel garantiza que los Listeners de eventos despachados
            // dentro de una transacción solo se ejecutan SI la transacción
            // hace commit exitosamente (no se ejecutan si hay rollback).
            CitaCancelada::dispatch($citaBloqueada);

            return $citaBloqueada;
        });
    }
}
