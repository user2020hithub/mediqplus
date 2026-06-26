<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;

class TotpSetupController extends Controller
{
    public function __construct(private TotpService $totpService) {}

    /**
     * Muestra el código QR para que el usuario configure su app autenticadora.
     * Solo accesible si el usuario aún NO tiene 2FA habilitado.
     */
    public function mostrarConfiguracion(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->habilitado_2fa) {
            return redirect()->route($usuario->rol . '.dashboard');
        }

        $secretoPlano = $this->totpService->generarSecreto($usuario);
        $qrUrl = $this->totpService->generarQrUrl($usuario, $secretoPlano);

        return view('auth.configurar-2fa', [
            'qrUrl' => $qrUrl,
            'secretoManual' => $secretoPlano, // por si no puede escanear el QR
        ]);
    }

    /**
     * Confirma que el usuario configuró bien su app, pidiéndole un primer
     * código válido antes de dar por completada la activación.
     */
    public function confirmarConfiguracion(Request $request)
    {
        $request->validate(['totp' => ['required', 'digits:6']]);

        $usuario = $request->user();

        if (!$this->totpService->validarCodigo($usuario, $request->totp)) {
            return back()->with('error', 'El código ingresado no es válido. Verifique su app autenticadora.');
        }

        return redirect()->route($usuario->rol . '.dashboard')
            ->with('exito', 'Autenticación de dos factores activada correctamente.');
    }
}
