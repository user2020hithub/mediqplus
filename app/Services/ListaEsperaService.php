<?php

namespace App\Services;

use App\Models\ListaEspera;
use App\Models\Paciente;
use App\Models\Auditoria;
use App\Events\SuscripcionListaEsperaCreada;
use Illuminate\Support\Facades\DB;

class ListaEsperaService
{
    /**
     * CU-13 — Crea una suscripción a lista de espera, validando que no
     * exista ya una activa idéntica (paso 4 / flujo 3a del SRS).
     */
    public function suscribir(
        Paciente $paciente,
        int $idEspecialidad,
        int $idSede,
        ?string $fechaInicioPref,
        ?string $fechaFinPref
    ): ListaEspera {
        return DB::transaction(function () use (
            $paciente, $idEspecialidad, $idSede, $fechaInicioPref, $fechaFinPref
        ) {
            // ── Paso 4 del SRS: prevención de duplicados ─────────────
            // No se usa lockForUpdate() aquí porque a diferencia de la
            // reserva de citas (Fase 4), dos suscripciones duplicadas no
            // generan un conflicto de recursos físicos — solo se evita
            // ensuciar la tabla con registros redundantes.
            $existeDuplicado = ListaEspera::where('id_paciente', $paciente->id_paciente)
                ->where('id_especialidad', $idEspecialidad)
                ->where('id_sede', $idSede)
                ->where('estado', 'Activa')
                ->exists();

            if ($existeDuplicado) {
                throw new \App\Exceptions\ReservaException(
                    'Ya posee una suscripción activa para esta especialidad y sede. Puede gestionarla desde "Mis Suscripciones".'
                );
            }

            // ── Paso 6 del SRS: vencimiento automático a 30 días ─────
            $suscripcion = ListaEspera::create([
                'id_paciente' => $paciente->id_paciente,
                'id_especialidad' => $idEspecialidad,
                'id_sede' => $idSede,
                'fecha_inicio_pref' => $fechaInicioPref,
                'fecha_fin_pref' => $fechaFinPref,
                'estado' => 'Activa',
                'consentimiento' => true,
                'fecha_vencimiento' => now()->addDays(30),
            ]);

            // ── Paso 11 del SRS: auditoría con evidencia del consentimiento ──
            Auditoria::registrar($paciente->usuario->id_usuario, 'SUSCRIPCION_LISTA_ESPERA',
                'lista_espera', $suscripcion->id_lista, [
                    'id_especialidad' => $idEspecialidad,
                    'id_sede' => $idSede,
                    'consentimiento' => true,
                ]);

            // ── Paso 9 del SRS: evento asíncrono para el correo ──────
            // Disparado DENTRO de la transacción — Laravel garantiza que
            // solo se ejecuta si el commit tiene éxito (mismo patrón de CU-07).
            SuscripcionListaEsperaCreada::dispatch($suscripcion);

            return $suscripcion;
        });
    }

    /**
     * Cancela una suscripción activa (mencionado en el paso 10 del SRS
     * como enlace "Cancelar" en el correo de confirmación).
     */
    public function cancelar(ListaEspera $suscripcion, int $idPacienteSolicitante): void
    {
        if ($suscripcion->id_paciente !== $idPacienteSolicitante) {
            throw new \App\Exceptions\AccesoNoAutorizadoException(
                'No tiene permisos para cancelar esta suscripción.'
            );
        }

        $suscripcion->update(['estado' => 'Cancelada']);
    }
}
