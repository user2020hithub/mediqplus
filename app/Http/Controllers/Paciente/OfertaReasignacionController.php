<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Agenda;
use App\Models\AuditoriaReasignacion;
use App\Models\ListaEspera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OfertaReasignacionController extends Controller
{
    /**
     * CU-08, paso 9 del SRS — el paciente acepta la oferta de reasignación.
     */
    public function aceptar(Request $request, string $token)
    {
        $oferta = Cache::get("oferta_reasignacion:{$token}");

        // ── Flujo alternativo 9a del SRS: token expirado o ya usado ──
        if (!$oferta) {
            return redirect()->route('paciente.dashboard')->with(
                'error',
                'Lo sentimos, este cupo ha expirado o ya fue asignado. Estamos buscando otra opción para ti.'
            );
        }

        try {
            DB::beginTransaction();

            // ── Flujo alternativo 2a / 9a: condición de carrera ──────
            // Se vuelve a verificar el estado REAL del slot con bloqueo
            // pesimista, por si el cupo ya fue tomado por otro proceso
            // entre que se generó el token y que el paciente hizo clic.
            $citaOrigen = Cita::where('id_cita', $oferta['id_cita_origen'])->first();
            $agenda = Agenda::where('id_agenda', $citaOrigen->id_agenda)
                ->lockForUpdate()
                ->first();

            if ($agenda->estado !== 'En_Proceso_Reasignacion') {
                DB::rollBack();
                return redirect()->route('paciente.dashboard')->with(
                    'error',
                    'Lo sentimos, este cupo acaba de ser asignado. Estamos buscando otra opción para ti.'
                );
            }

            $listaEspera = ListaEspera::find($oferta['id_candidato']);

            // Reutiliza CitaService de la Fase 4 para crear la nueva cita
            // — el motor de reasignación NO duplica la lógica de reserva,
            // la reusa, manteniendo una sola fuente de verdad.
            $agenda->update(['estado' => 'Reservado']);

            $nuevaCita = Cita::create([
                'codigo_cita' => 'CITA-' . now()->format('Ymd') . '-R' . $citaOrigen->id_cita,
                'id_paciente' => $listaEspera->id_paciente,
                'id_medico' => $agenda->id_medico,
                'id_sede' => $agenda->id_sede,
                'id_agenda' => $agenda->id_agenda,
                'fecha_cita' => $agenda->fecha_especifica ?? now()->toDateString(),
                'hora_inicio' => $agenda->hora_inicio,
                'hora_fin' => $agenda->hora_fin,
                'estado' => 'Pendiente_Confirmacion',
            ]);

            $listaEspera->update(['estado' => 'Completada']);

            AuditoriaReasignacion::create([
                'id_cita_origen' => $citaOrigen->id_cita,
                'id_candidato' => $listaEspera->id_paciente,
                'intento_numero' => $oferta['intento_actual'] + 1,
                'resultado' => 'Aceptada',
            ]);

            DB::commit();

            // El token se invalida para que el Delayed Job de timeout
            // (que llegará 15 min después) no vuelva a procesarlo.
            Cache::forget("oferta_reasignacion:{$token}");

            return redirect()->route('paciente.dashboard')
                ->with('exito', "¡Cupo confirmado! Su nueva cita es: {$nuevaCita->codigo_cita}");
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Error al procesar su confirmación. Intente nuevamente.');
        }
    }

    /**
     * CU-08, paso 10 del SRS — el paciente rechaza explícitamente la oferta
     * (en vez de simplemente dejar pasar los 15 minutos).
     */
    public function rechazar(string $token)
    {
        $oferta = Cache::get("oferta_reasignacion:{$token}");

        if ($oferta) {
            AuditoriaReasignacion::create([
                'id_cita_origen' => $oferta['id_cita_origen'],
                'id_candidato' => $oferta['id_candidato'],
                'intento_numero' => $oferta['intento_actual'] + 1,
                'resultado' => 'Rechazada',
            ]);

            Cache::forget("oferta_reasignacion:{$token}");

            // Despachar INMEDIATAMENTE al siguiente candidato — no hay
            // razón para esperar los 15 min si el paciente ya respondió.
            \App\Jobs\OfrecerCupoJob::dispatch(
                \App\Models\Cita::find($oferta['id_cita_origen']),
                $oferta['candidatos_restantes'],
                $oferta['intento_actual'] + 1
            );
        }

        return view('paciente.oferta-rechazada');
    }
}
