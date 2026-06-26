<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Toda la lógica de negocio de la reserva vive aquí, NO en el Controller.
// Esto permite reutilizarla (ej. desde el motor de reasignación en la
// Fase 5) y probarla unitariamente sin simular peticiones HTTP completas.
class CitaService
{
    private const LIMITE_CITAS_ACTIVAS = 3;
    private const ESTADOS_ACTIVOS = ['Pendiente_Confirmacion', 'Confirmada'];
    private const ANTICIPACION_MINIMA_HORAS = 2;

    /**
     * Reserva un slot de agenda para un paciente, aplicando TODAS las
     * validaciones de negocio del SRS dentro de una sola transacción ACID.
     */
    public function reservar(Paciente $paciente, int $idAgenda, ?string $motivoConsulta): Cita
    {
        return DB::transaction(function () use ($paciente, $idAgenda, $motivoConsulta) {

            // ── Paso 5 del SRS: bloqueo pesimista sobre el slot ──────────
            // lockForUpdate() genera un SELECT ... FOR UPDATE en MySQL —
            // ningún otro proceso puede leer/modificar esta fila hasta que
            // esta transacción termine (commit o rollback). Esto es lo que
            // previene el doble-booking del mismo slot.
            $agenda = Agenda::where('id_agenda', $idAgenda)
                ->lockForUpdate()
                ->first();

            if (!$agenda) {
                throw new \App\Exceptions\ReservaException('El horario seleccionado no existe.');
            }

            // ── Validación 1: el slot debe seguir Disponible (flujo 5a) ──
            // Si otro paciente lo reservó milisegundos antes, esta condición
            // ya lo habrá cambiado a "Reservado" y aquí se detecta.
            if ($agenda->estado !== 'Disponible') {
                throw new \App\Exceptions\ReservaException(
                    'El horario seleccionado ya no está disponible. Por favor, elija otro turno.'
                );
            }

            // ── Validación 2: límite de 3 citas activas (flujo 6a) ───────
            $citasActivas = Cita::where('id_paciente', $paciente->id_paciente)
                ->whereIn('estado', self::ESTADOS_ACTIVOS)
                ->count();

            if ($citasActivas >= self::LIMITE_CITAS_ACTIVAS) {
                throw new \App\Exceptions\ReservaException(
                    'Ha alcanzado el límite máximo de 3 citas activas. Debe cancelar una existente para reservar.'
                );
            }

            // ── Validación 3: anticipación mínima de 2 horas (flujo 6c) ──
            $fechaHoraCita = \Carbon\Carbon::parse(
                ($agenda->fecha_especifica ?? $this->proximaFechaParaDiaSemana($agenda->dia_semana))
                    . ' ' . $agenda->hora_inicio
            );

            if (now()->diffInHours($fechaHoraCita, false) < self::ANTICIPACION_MINIMA_HORAS) {
                throw new \App\Exceptions\ReservaException(
                    'La reserva debe realizarse con un mínimo de 2 horas de anticipación.'
                );
            }

            // ── Validación 4: no solapamiento con otras citas del paciente (6b) ──
            $seSolapa = Cita::where('id_paciente', $paciente->id_paciente)
                ->whereIn('estado', self::ESTADOS_ACTIVOS)
                ->where('fecha_cita', $fechaHoraCita->toDateString())
                ->where(function ($q) use ($agenda) {
                    $q->where('hora_inicio', '<', $agenda->hora_fin)
                        ->where('hora_fin', '>', $agenda->hora_inicio);
                })
                ->exists();

            if ($seSolapa) {
                throw new \App\Exceptions\ReservaException(
                    'Ya tiene una cita programada en este rango horario.'
                );
            }

            // ── Todas las validaciones pasaron: ejecutar la reserva ──────
            $agenda->update(['estado' => 'Reservado']);

            $cita = Cita::create([
                'codigo_cita' => $this->generarCodigoCita(),
                'id_paciente' => $paciente->id_paciente,
                'id_medico' => $agenda->id_medico,
                'id_sede' => $agenda->id_sede,
                'id_agenda' => $agenda->id_agenda,
                'fecha_cita' => $fechaHoraCita->toDateString(),
                'hora_inicio' => $agenda->hora_inicio,
                'hora_fin' => $agenda->hora_fin,
                'estado' => 'Pendiente_Confirmacion',
                'motivo_consulta' => $motivoConsulta,
            ]);

            Auditoria::registrar(
                $paciente->usuario->id_usuario,
                'RESERVA_CITA',
                'citas',
                $cita->id_cita,
                ['codigo_cita' => $cita->codigo_cita]
            );

            // ── E-01 del CU-03: el envío del correo de confirmación se
            // encola de forma asíncrona en la Fase 5 (CU-09). Por ahora,
            // la transacción de la cita NO depende de ese envío —
            // exactamente como exige la excepción E-01 del SRS (la
            // integridad de la agenda tiene prioridad sobre el correo).

            return $cita;
        });
    }

    /**
     * Genera un código único tipo CITA-YYYYMMDD-XXXX usando un contador
     * del día (4 dígitos) para mantenerlo corto y legible.
     */
    private function generarCodigoCita(): string
    {
        $fecha = now()->format('Ymd');
        $contador = Cita::whereDate('fecha_creacion', now()->toDateString())->count() + 1;
        return sprintf('CITA-%s-%04d', $fecha, $contador);
    }

    /**
     * Para agendas Regulares (sin fecha_especifica), calcula la próxima
     * fecha calendario que corresponde al dia_semana configurado.
     */
    private function proximaFechaParaDiaSemana(int $diaSemana): string
    {
        // Carbon usa 0=Domingo..6=Sábado; nuestro dia_semana usa 1=Lunes..7=Domingo,
        // por eso se convierte el 7 (Domingo) al 0 que espera Carbon.
        $diaCarbon = $diaSemana === 7 ? 0 : $diaSemana;
        return now()->next($diaCarbon)->toDateString();
    }
}
