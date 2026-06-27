<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistroPacienteRequest;
use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\TotpService;

class AuthController extends Controller
{
    public function __construct(private TotpService $totpService) {}

    public function showRegistro()
    {
        return view('auth.registro');
    }

    /**
     * CU-01: Crear cuenta de acceso para agendar citas.
     * Solo aplica al rol "paciente" — médicos y admins se crean desde
     * el módulo de Admin (CU-05), no por auto-registro.
     */
    public function registro(RegistroPacienteRequest $request)
    {
        // Transacción ACID: si falla cualquier paso, no debe quedar
        // ni el Usuario ni el Paciente creados a medias.
        try {
            DB::beginTransaction();

            // Paso 1: crear el registro base en "usuarios"
            $usuario = Usuario::create([
                'rol' => 'paciente',
                'correo_electronico' => $request->correo_electronico,
                // bcrypt con costo >= 12, tal como exige el SRS (RNF-SEGU-02)
                'password_hash' => Hash::make($request->password, ['rounds' => 12]),
                'estado_cuenta' => 'Pendiente_Verificacion',
            ]);

            // Paso 2: crear el perfil específico de Paciente, ligado al usuario
            $tokenActivacion = Str::random(64);

            Paciente::create([
                'id_usuario' => $usuario->id_usuario,
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'nombres_apellidos' => $request->nombres_apellidos,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'telefono_movil' => $request->telefono_movil,
                'acepta_privacidad' => true,
                'token_activacion' => $tokenActivacion,
            ]);

            // Paso 3: registrar el evento en Auditoria (trazabilidad RNF-SEGU)
            Auditoria::registrar(
                $usuario->id_usuario,
                'REGISTRO_PACIENTE',
                'usuarios',
                $usuario->id_usuario,
                ['correo' => $usuario->correo_electronico]
            );

            DB::commit();

            // Paso 4: encolar el correo de activación (no bloquea la respuesta)
            // El Mailable ActivacionCuentaMail se implementa en la Fase 5 (CU-09),
            // por ahora se deja preparado el dispatch comentado:
            // Mail::to($usuario->correo_electronico)->queue(new ActivacionCuentaMail($tokenActivacion));

            return redirect()->route('auth.login')
                ->with('exito', 'Registro exitoso. Revise su correo electrónico para activar su cuenta.');
        } catch (\Exception $e) {
            DB::rollBack();

            // No se expone el detalle técnico al usuario final (buena práctica de seguridad)
            report($e);

            return back()->withInput()->with(
                'error',
                'Error de conexión. Intente nuevamente en unos minutos.'
            );
        }
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * CU-02 + CU-11 (simplificado, sin token temporal — acordado para este proyecto).
     *
     * Flujo:
     *   1er submit (sin campo totp): valida correo+contraseña.
     *      - Si el rol es "paciente" → genera token Sanctum y entra directo.
     *      - Si el rol es "admin" o "medico" → NO genera token todavía;
     *        responde mostrando de nuevo el formulario con el campo TOTP visible.
     *   2do submit (con campo totp presente): valida correo+contraseña+totp
     *      juntos en una sola petición y, si todo es correcto, genera el token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'identificador' => ['required', 'string'], // correo o documento
            'password' => ['required', 'string'],
            'totp' => ['nullable', 'digits:6'],
        ]);

        // Buscar por correo o por documento de identidad (paciente)
        $usuario = Usuario::where('correo_electronico', $request->identificador)->first();

        if (!$usuario) {
            // Mensaje genérico — no revela si el usuario existe o no (anti-enumeración, SRS 4a)
            return back()->withInput()->with('error', 'Usuario o contraseña incorrectos.');
        }

        // ── Verificar bloqueo temporal por intentos fallidos previos ──────
        if ($usuario->bloqueado_hasta && now()->lt($usuario->bloqueado_hasta)) {
            return back()->with(
                'error',
                'Demasiados intentos fallidos. Su cuenta ha sido bloqueada temporalmente por seguridad.'
            );
        }

        // ── Verificar contraseña (bcrypt) ─────────────────────────────────
        if (!Hash::check($request->password, $usuario->password_hash)) {
            $this->registrarIntentoFallido($usuario);
            return back()->withInput()->with('error', 'Usuario o contraseña incorrectos.');
        }

        // ── Verificar que la cuenta esté activa ───────────────────────────
        if ($usuario->estado_cuenta !== 'Activo') {
            return back()->with(
                'error',
                'Su cuenta no está activa. Por favor, verifique su correo electrónico o contacte a soporte administrativo.'
            );
        }

        // ── Si el rol requiere 2FA (admin o medico) ───────────────────────
        $requiere2FA = in_array($usuario->rol, ['admin', 'medico']);

        if ($requiere2FA) {
            // Si todavía no envió el código TOTP, se le vuelve a mostrar el
            // formulario de login con el campo TOTP visible (segundo submit).
            if (!$request->filled('totp')) {
                return back()->withInput()->with('mostrar_totp', true)
                    ->with('info', 'Ingrese el código de 6 dígitos de su app autenticadora.');
            }

            // Segundo submit: validar el código TOTP
            if (!$this->validarTotp($usuario, $request->totp)) {
                $this->registrarIntentoFallido($usuario, esFallo2FA: true);
                return back()->withInput()->with('mostrar_totp', true)->with(
                    'error',
                    'El código de verificación es incorrecto o ha expirado.'
                );
            }

            // 2FA correcto: resetear su contador específico
            $usuario->update(['intentos_fallidos_2fa' => 0]);
        }

        // ── Éxito: resetear contadores, generar token Sanctum ─────────────
        $usuario->update([
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'ultimo_login' => now(),
        ]);

        // Duración del token: 30 min normal, 7 días si marcó "Recordar sesión"
        $token = $usuario->createToken(
            'token-sesion',
            ['*'],
            $request->boolean('recordar') ? now()->addDays(7) : now()->addMinutes(30)
        );

        Auditoria::registrar($usuario->id_usuario, 'LOGIN_EXITOSO', 'usuarios', $usuario->id_usuario, [
            'rol' => $usuario->rol,
        ]);

        // Iniciar sesión también vía guard "web" (cookies de sesión Blade)
        auth('web')->login($usuario);

        $dashboard = match ($usuario->rol) {
            'paciente' => 'paciente.dashboard',
            'medico'   => 'medico.dashboard',
            'admin'    => 'admin.dashboard',
        };

        return redirect()->route($dashboard);
    }

    /**
     * Valida el código TOTP de 6 dígitos contra el secreto del usuario.
     */
    private function validarTotp(Usuario $usuario, string $codigo): bool
    {
        return $this->totpService->validarCodigo($usuario, $codigo);
    }
    /**
     * Incrementa el contador de intentos fallidos (login o 2FA) y bloquea
     * la cuenta por 10 minutos si se alcanzan 3 intentos (regla del SRS).
     */
    private function registrarIntentoFallido(Usuario $usuario, bool $esFallo2FA = false): void
    {
        $campo = $esFallo2FA ? 'intentos_fallidos_2fa' : 'intentos_fallidos';
        $usuario->increment($campo);

        $totalIntentos = $usuario->intentos_fallidos + $usuario->intentos_fallidos_2fa;

        if ($totalIntentos >= 3) {
            $usuario->update(['bloqueado_hasta' => now()->addMinutes(10)]);
        }

        Auditoria::registrar($usuario->id_usuario, 'LOGIN_FALLIDO', 'usuarios', $usuario->id_usuario, [
            'tipo' => $esFallo2FA ? 'codigo_2fa' : 'password',
        ]);
    }

    public function logout(Request $request)
    {
        // Como el login usa auth('web')->login() (sesión, no token API),
        // no existe un Access Token real que borrar — currentAccessToken()
        // devuelve un TransientToken que no tiene método delete().
        auth('web')->logout();

        // Invalida la sesión actual y regenera el token CSRF, por seguridad
        // (evita que la sesión cerrada se reutilice con "atrás" del navegador).
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login')->with('exito', 'Sesión cerrada correctamente.');
    }
}
