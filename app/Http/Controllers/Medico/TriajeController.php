<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;

class TriajeController extends Controller
{
    /**
     * Paso 12 del SRS — el médico ve el resumen del triaje, con los
     * campos cifrados ya DESENCRIPTADOS automáticamente por los
     * accessors del Model (getDetalleAlergiasAttribute, etc.) —
     * el Controller ni siquiera necesita saber que están cifrados.
     */
    public function verResumen(Request $request, Cita $cita)
    {
        // Solo el médico asignado a ESTA cita puede ver el triaje
        // (regla de negocio explícita del SRS: "visible solo para el
        // médico asignado").
        if ($cita->id_medico !== $request->user()->medico->id_medico) {
            abort(403, 'Acceso denegado.');
        }

        $triaje = $cita->triaje; // Las propiedades cifradas se desencriptan solas al accederlas

        return view('medico.triaje.resumen', compact('cita', 'triaje'));
    }
}
