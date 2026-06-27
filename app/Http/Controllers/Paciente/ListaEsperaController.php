<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuscribirseListaEsperaRequest;
use App\Models\ListaEspera;
use App\Services\ListaEsperaService;
use App\Exceptions\ReservaException;
use App\Exceptions\AccesoNoAutorizadoException;
use Illuminate\Http\Request;

class ListaEsperaController extends Controller
{
    public function __construct(private ListaEsperaService $service) {}

    /**
     * Muestra el formulario, pre-llenando especialidad/sede si vino
     * desde el gancho de CitaController (query string).
     */
    public function mostrarFormulario(Request $request)
    {
        $especialidades = \App\Models\Especialidad::activas()->get();
        $sedes = \App\Models\Sede::activas()->get();

        return view('paciente.lista-espera.crear', [
            'especialidades' => $especialidades,
            'sedes' => $sedes,
            'idEspecialidadPreseleccionada' => $request->id_especialidad,
            'idSedePreseleccionada' => $request->id_sede,
        ]);
    }

    public function suscribir(SuscribirseListaEsperaRequest $request)
    {
        try {
            $this->service->suscribir(
                $request->user()->paciente,
                (int) $request->id_especialidad,
                (int) $request->id_sede,
                $request->fecha_inicio_pref,
                $request->fecha_fin_pref
            );

            return redirect()->route('paciente.dashboard')->with('exito',
                'Suscripción exitosa. Le notificaremos cuando se libere un cupo (válida por 30 días).');

        } catch (ReservaException $e) {
            return back()->withInput()->with('error', $e->getMessage());

        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'Error de conexión. Intente nuevamente en unos minutos.');
        }
    }

    /**
     * Listado de "Mis Suscripciones" (mencionado en el paso 12 del SRS).
     */
    public function misSuscripciones(Request $request)
    {
        $suscripciones = ListaEspera::where('id_paciente', $request->user()->paciente->id_paciente)
            ->with(['especialidad', 'sede'])
            ->orderByDesc('fecha_creacion')
            ->get();

        return view('paciente.lista-espera.mis-suscripciones', compact('suscripciones'));
    }

    public function cancelar(Request $request, ListaEspera $listaEspera)
    {
        try {
            $this->service->cancelar($listaEspera, $request->user()->paciente->id_paciente);
            return back()->with('exito', 'Suscripción cancelada correctamente.');
        } catch (AccesoNoAutorizadoException $e) {
            abort(403, $e->getMessage());
        }
    }
}
