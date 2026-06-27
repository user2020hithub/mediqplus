<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Services\CitaService;
use App\Exceptions\ReservaException;
use Illuminate\Http\Request;
use App\Services\CancelacionService;
use App\Exceptions\AccesoNoAutorizadoException;
use App\Models\Cita;


class CitaController extends Controller
{
    // Se agrega CancelacionService al constructor existente (inyección múltiple)
    public function __construct(
        private CitaService $citaService,
        private CancelacionService $cancelacionService
    ) {}


    /**
     * Wizard de reserva — Paso 1: búsqueda de disponibilidad por filtros.
     */
    public function buscarDisponibilidad(Request $request)
    {
        // CORRECCIÓN: si todavía no se envió ninguna búsqueda (el paciente
        // recién entró desde el botón "Reservar cita" del dashboard), se
        // muestra el formulario VACÍO — sin validar ni consultar nada.
        // La vista ya está preparada para esto: usa @if(isset($slots))
        // y solo muestra resultados cuando esa variable SÍ llega.
        if (!$request->filled('id_especialidad')) {
            return view('paciente.citas.disponibilidad');
        }

        // A partir de aquí, el paciente YA envió el formulario de búsqueda
        // (llegó id_especialidad) — recién entonces se valida y se consulta.
        $request->validate([
            'id_especialidad' => ['required', 'exists:especialidades,id_especialidad'],
            'id_sede' => ['nullable', 'exists:sedes,id_sede'],
        ]);

        $slots = Agenda::disponibles()
            ->whereHas('medico', function ($q) use ($request) {
                $q->where('id_especialidad', $request->id_especialidad)
                    ->where('estado', 'Activo');
            })
            ->when($request->id_sede, fn($q) => $q->where('id_sede', $request->id_sede))
            ->with(['medico', 'sede'])
            ->get();

        if ($slots->isEmpty()) {
            return redirect()->route('paciente.lista-espera.crear', [
                'id_especialidad' => $request->id_especialidad,
                'id_sede' => $request->id_sede,
            ])->with('info', 'No hay horarios disponibles en este momento. Complete el formulario para suscribirse a la lista de espera.');
        }

        return view('paciente.citas.disponibilidad', compact('slots'));
    }

    /**
     * CU-03 — Confirmar la reserva de un slot específico (Paso 2 del wizard).
     */
    public function reservar(Request $request)
    {
        $request->validate([
            'id_agenda' => ['required', 'exists:agenda,id_agenda'],
            // RNF-OTR-01: el motivo de consulta es opcional y limitado a 255
            // caracteres, cumpliendo el principio de minimización de datos.
            'motivo_consulta' => ['nullable', 'string', 'max:255'],
        ]);

        $paciente = $request->user()->paciente;

        try {
            $cita = $this->citaService->reservar(
                $paciente,
                (int) $request->id_agenda,
                $request->motivo_consulta
            );

            return redirect()->route('paciente.dashboard')
                ->with('exito', "Cita reservada exitosamente. Su código es: {$cita->codigo_cita}");
        } catch (ReservaException $e) {
            // Errores de REGLA DE NEGOCIO (slot ocupado, límite alcanzado, etc.)
            // — se muestran tal cual al usuario, ya que son mensajes pensados
            // para que él los entienda y actúe en consecuencia.
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            // Errores TÉCNICOS (conexión, timeout, etc.) — nunca se expone
            // el detalle interno al usuario final (flujo alternativo 4a del SRS).
            report($e);
            return back()->withInput()->with('error', 'Error de conexión. Intente nuevamente en unos minutos.');
        }
    }

    /**
     * CU-07 — Cancelar una cita propia.
     */
    public function cancelar(Request $request, Cita $cita)
    {
        $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'max:100'],
            'confirma' => ['required', 'accepted'], // checkbox de confirmación (flujo 1a)
        ]);

        try {
            $this->cancelacionService->cancelar(
                $cita,
                $request->user()->paciente->id_paciente,
                $request->motivo_cancelacion
            );

            return redirect()->route('paciente.dashboard')
                ->with('exito', 'Cita cancelada exitosamente. El cupo ha sido liberado.');
        } catch (AccesoNoAutorizadoException $e) {
            // HTTP 403 explícito, tal como exige el flujo alternativo 5b del SRS
            abort(403, $e->getMessage());
        } catch (ReservaException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Error al procesar la cancelación. Intente nuevamente.');
        }
    }
}
