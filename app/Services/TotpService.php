<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TotpService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Genera un nuevo secreto TOTP y lo guarda CIFRADO (AES-256) en el usuario.
     * Se llama una sola vez, durante el proceso de "activar 2FA" (Paso 6).
     */
    public function generarSecreto(Usuario $usuario): string
    {
        $secretoPlano = $this->google2fa->generateSecretKey();

        $usuario->update([
            // Cifrado en reposo — el mismo patrón usado en Triaje (Fase 2)
            'totp_secret' => Crypt::encryptString($secretoPlano),
            'habilitado_2fa' => true,
        ]);

        // Se retorna el secreto EN TEXTO PLANO solo esta vez, para que el
        // usuario pueda escanearlo en su app autenticadora (QR). Nunca se
        // vuelve a exponer en texto plano después de este punto.
        return $secretoPlano;
    }

    /**
     * Genera la URL del código QR para escanear con Google Authenticator.
     */
    public function generarQrUrl(Usuario $usuario, string $secretoPlano): string
    {
        return $this->google2fa->getQRCodeUrl(
            'MEDIQ+ TuSalud',
            $usuario->correo_electronico,
            $secretoPlano
        );
    }

    /**
     * Valida un código TOTP de 6 dígitos contra el secreto cifrado del usuario.
     * Ventana de tolerancia: ±30 segundos (1 "window" de Google2FA), tal
     * como especifica el SRS.
     */
    public function validarCodigo(Usuario $usuario, string $codigo): bool
    {
        if (!$usuario->totp_secret) {
            return false;
        }

        try {
            $secretoPlano = Crypt::decryptString($usuario->totp_secret);
        } catch (\Exception $e) {
            // Fallo en el descifrado (clave APP_KEY incorrecta, dato corrupto, etc.)
            // El SRS pide aquí registrar una alerta crítica.
            report($e);
            return false;
        }

        // window=1 → tolera el código del intervalo anterior y posterior
        // (cada intervalo TOTP dura 30s, por lo que window=1 = ±30s)
        return $this->google2fa->verifyKey($secretoPlano, $codigo, window: 1);
    }
}
