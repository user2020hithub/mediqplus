<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\AuditoriaContingencia;
use App\Events\ReprogramacionMasivaEjecutada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReprogramacionMasivaService
{
    private const ESTADOS_ELEGIBLES = ['Pendiente_Confirmacion', 'Confirmada'];

    /**
     * CU-12 — Ejecuta el batch update completo dentro de una transacción
     * ACID con bloqueo pesimista, igual al patrón ya usado en CitaService
     * (Fase 4) y CancelacionService (Fase 5).
     *
     * @return array{cantidad: int, citaIds: array} resumen para la respuesta
     */
    public function ejecutar(
        ?int $idMedico,
        ?int $idSede,
        ?string $fechaDesde,
        ?string $fechaHasta,
        string $motivo,
        string $accion, // 'A' o 'B'
        ?string $nuevaFecha,
        ?string $nuevaHoraInicio,
        int $idAdminResponsable
    ): array {
        return DB::transaction(function () use (
            $idMedico,
            $idSede,
            $fechaDesde,
            $fechaHasta,
            $motivo,
            $accion,
            $nuevaFecha,
            $nuevaHoraInicio,
            $idAdminResponsable
        ) {
            // ── Paso 4 del SRS: bloqueo pesimista sobre las citas filtradas ──
            $query = Cita::whereIn('estado', self::ESTADOS_ELEGIBLES)
                ->lockForUpdate();

            if ($idMedico) {
                $query->where('id_medico', $idMedico);
            }
            if ($idSede) {
                $query->where('id_sede', $idSede);
            }
            if ($fechaDesde && $fechaHasta) {
                $query->whereBetween('fecha_cita', [$fechaDesde, $fechaHasta]);
            }

            $citasAfectadas = $query->get();

            // ── Paso 5 / flujo alternativo 5a del SRS ────────────────
            if ($citasAfectadas->isEmpty()) {
                throw new \App\Exceptions\ReservaException(
                    'No se encontraron citas elegibles para los filtros seleccionados.'
                );
            }

            // ── Paso 6 del SRS: aplicar la acción elegida (Batch Update) ──
            if ($accion === 'A') {
                // Opción A: nueva fecha/hora fija, igual para todas las citas
                Cita::whereIn('id_cita', $citasAfectadas->pluck('id_cita'))
                    ->update([
                        'fecha_cita' => $nuevaFecha,
                        'hora_inicio' => $nuevaHoraInicio,
                        'fecha_actualizacion' => now(),
                    ]);
            } else {
                // Opción B: cada cita recibe SU PROPIO token único de 72h
                // (no se puede hacer en un solo UPDATE masivo porque cada
                // fila necesita un token DISTINTO — Laravel itera, pero
                // sigue dentro de la MISMA transacción ACID).
                foreach ($citasAfectadas as $cita) {
                    $cita->update([
                        'estado' => 'Pendiente_Reprogramacion',
                        'token_reprogramacion' => Str::random(64),
                        // La expiración de 72h se deriva de fecha_actualizacion
                        // al momento de validar el token, igual que se hizo
                        // con el token de activación de médicos (Fase 4) —
                        // no se agrega una columna nueva para esto.
                    ]);
                }
            }

            // ── Paso 10 del SRS: auditoría con hash SHA-256 ──────────
            // Se reutiliza el helper generarHash() ya definido en el
            // Model AuditoriaContingencia desde la Fase 2 — no se
            // repite la lógica de hashing aquí.
            $hash = AuditoriaContingencia::generarHash(
                $idAdminResponsable,
                $motivo,
                $citasAfectadas->count()
            );

            AuditoriaContingencia::create([
                'id_usuario_responsable' => $idAdminResponsable,
                'motivo' => $motivo,
                'detalle_motivo' => "Acción {$accion} aplicada a {$citasAfectadas->count()} citas",
                'cantidad_afectada' => $citasAfectadas->count(),
                'accion_ejecutada' => $accion === 'A' ? 'Reprogramacion_Fija' : 'Token_Reprogramacion',
                'hash_integridad' => $hash,
            ]);

            // ── Paso 8 del SRS: evento asíncrono de alta prioridad ───
            // Disparado dentro de la transacción — solo se ejecuta tras
            // el commit exitoso (mismo patrón usado en toda la Fase 5).
            ReprogramacionMasivaEjecutada::dispatch(
                $citasAfectadas->pluck('id_cita')->toArray(),
                $motivo,
                $accion
            );

            return [
                'cantidad' => $citasAfectadas->count(),
                'citaIds' => $citasAfectadas->pluck('id_cita')->toArray(),
            ];
        });
    }
}
