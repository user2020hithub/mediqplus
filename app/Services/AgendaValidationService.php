<?php

namespace App\Services;

use App\Models\Agenda;
use Carbon\Carbon;

// Service dedicado SOLO a las validaciones de agenda. Se aísla aquí
// (en vez de meterlo directo en el Controller) para poder probarlo
// de forma unitaria en la Fase 8 sin necesidad de simular peticiones HTTP.
class AgendaValidationService
{
    /**
     * Tiempo mínimo de traslado exigido entre sedes distintas, el mismo día,
     * para un mismo médico (regla de negocio confirmada para CU-06).
     */
    private const MARGEN_TRASLADO_MINUTOS = 120; //60 min

    /**
     * Verifica que el nuevo bloque NO se solape con ningún bloque existente
     * del mismo médico, en el mismo día de la semana (o misma fecha específica
     * si es una excepción tipo vacaciones/licencia).
     *
     * Lógica de solapamiento (la misma que especifica el SRS):
     *   NOT (nueva_hora_inicio >= existente_hora_fin OR nueva_hora_fin <= existente_hora_inicio)
     */
    public function haySolapamiento(
        int $idMedico,
        ?int $diaSemana,
        ?string $fechaEspecifica,
        string $horaInicio,
        string $horaFin,
        ?int $idAgendaExcluir = null
    ): bool {
        $query = Agenda::where('id_medico', $idMedico)
            ->where('estado', '!=', 'Bloqueado'); // los bloqueados no compiten por horario

        // Las agendas Regulares se comparan por día de semana; las
        // Excepciones se comparan por fecha específica.
        if ($fechaEspecifica) {
            $query->where('fecha_especifica', $fechaEspecifica);
        } else {
            $query->where('dia_semana', $diaSemana);
        }

        if ($idAgendaExcluir) {
            $query->where('id_agenda', '!=', $idAgendaExcluir); // al editar, no comparar contra sí mismo
        }

        // Dos rangos de horas se solapan si: inicio_nuevo < fin_existente
        // Y fin_nuevo > inicio_existente. Si existe al menos 1 fila que
        // cumple esto, hay conflicto de horario.
        $existeSolapado = $query->where(function ($q) use ($horaInicio, $horaFin) {
            $q->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio);
        })->exists();

        return $existeSolapado;
    }

    /**
     * Verifica el margen mínimo de traslado (120 min) entre sedes distintas,
     * el mismo médico, el mismo día.
     *
     * Ejemplo confirmado contigo: Sede A termina 13:00, Sede B empieza 15:00
     * → margen de 120 min → VÁLIDO. Si Sede B empezara a las 13:30
     * → margen de 30 min → INVÁLIDO (no cumple el mínimo de 120).
     */
    public function cumpleMargenTraslado(
        int $idMedico,
        int $idSedeNueva,
        ?int $diaSemana,
        ?string $fechaEspecifica,
        string $horaInicio,
        string $horaFin,
        ?int $idAgendaExcluir = null
    ): bool {
        // Solo nos interesan los bloques de OTRAS sedes (distinta a la nueva),
        // porque el margen de traslado solo aplica al cambiar de ubicación física.
        $query = Agenda::where('id_medico', $idMedico)
            ->where('id_sede', '!=', $idSedeNueva)
            ->where('estado', '!=', 'Bloqueado');

        if ($fechaEspecifica) {
            $query->where('fecha_especifica', $fechaEspecifica);
        } else {
            $query->where('dia_semana', $diaSemana);
        }

        if ($idAgendaExcluir) {
            $query->where('id_agenda', '!=', $idAgendaExcluir);
        }

        $bloquesOtrasSedes = $query->get();

        $nuevoInicio = Carbon::createFromFormat('H:i:s', $horaInicio);
        $nuevoFin = Carbon::createFromFormat('H:i:s', $horaFin);

        foreach ($bloquesOtrasSedes as $bloque) {
            $otroInicio = Carbon::createFromFormat('H:i:s', $bloque->hora_inicio);
            $otroFin = Carbon::createFromFormat('H:i:s', $bloque->hora_fin);

            // Caso 1: el bloque NUEVO empieza DESPUÉS del bloque EXISTENTE
            // (el médico llega desde la otra sede) → se valida el margen
            // entre el fin del bloque anterior y el inicio del nuevo.
            if ($nuevoInicio->gte($otroFin)) {
                $margenReal = $otroFin->diffInMinutes($nuevoInicio);
                if ($margenReal < self::MARGEN_TRASLADO_MINUTOS) {
                    return false; // no cumple el margen mínimo
                }
            }

            // Caso 2: el bloque NUEVO termina ANTES de que empiece el bloque
            // EXISTENTE (el médico se va HACIA la otra sede después) → se
            // valida el margen entre el fin del nuevo y el inicio del existente.
            if ($nuevoFin->lte($otroInicio)) {
                $margenReal = $nuevoFin->diffInMinutes($otroInicio);
                if ($margenReal < self::MARGEN_TRASLADO_MINUTOS) {
                    return false;
                }
            }
        }

        return true; // No hay conflicto de margen con ninguna otra sede ese día
    }
}
