<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrarMedicoRequest;
use App\Models\Usuario;
use App\Models\Medico;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\CredencialesMedicoMail;
use Illuminate\Support\Facades\Mail;

class MedicoController extends Controller
{
    public function index()
    {
        // Listado paginado — usado en la vista de gestión de médicos
        $medicos = Medico::with(['especialidad', 'sedes'])->paginate(15);
        return view('admin.medicos.index', compact('medicos'));
    }

    public function create()
    {
        $especialidades = \App\Models\Especialidad::activas()->get();
        $sedes = \App\Models\Sede::activas()->get();
        return view('admin.medicos.create', compact('especialidades', 'sedes'));
    }

    /**
     * CU-05 — Alta de médico. Genera credenciales automáticamente, las
     * envía por correo (CU-09, conectado en la Fase 5) y registra el
     * evento de auditoría con el diff de cambios (RNF-OTR-01).
     */
    public function store(RegistrarMedicoRequest $request)
    {
        try {
            // Transacción ACID: si falla cualquier paso (usuario, médico o
            // asignación de sedes), no debe quedar nada creado a medias.
            DB::beginTransaction();

            // ── Generar contraseña aleatoria segura (RF-05, paso 4) ──────
            // Str::password() de Laravel ya garantiza la mezcla exigida
            // por el SRS: mín. 12 caracteres, 1 mayúscula, 1 número, 1 especial.
            $passwordTemporal = Str::password(12);

            $usuario = Usuario::create([
                'rol' => 'medico',
                'correo_electronico' => $request->correo_electronico,
                // bcrypt costo >= 12, igual que en el registro de pacientes (Fase 3)
                'password_hash' => Hash::make($passwordTemporal, ['rounds' => 12]),
                'estado_cuenta' => 'Pendiente_Verificacion',
            ]);

            // Token de activación (RF-05, paso 4). NOTA: no se agregó una
            // columna nueva para la expiración de 24h — se calcula comparando
            // fecha_creacion + 24h en el momento de validar el token, para no
            // modificar el esquema de la Fase 2 por algo que se puede derivar.
            $tokenActivacion = Str::random(64);

            $medico = Medico::create([
                'id_usuario' => $usuario->id_usuario,
                'id_especialidad' => $request->id_especialidad,
                'nombres_apellidos' => $request->nombres_apellidos,
                'dni' => $request->dni,
                'colegiatura' => $request->colegiatura,
                'telefono' => $request->telefono,
                'estado' => 'Activo',
                'token_activacion' => $tokenActivacion,
            ]);

            // Asignar las sedes seleccionadas (relación N:M vía medico_sede)
            foreach ($request->sedes as $idSede) {
                \App\Models\MedicoSede::create([
                    'id_medico' => $medico->id_medico,
                    'id_sede' => $idSede,
                    'activo' => true,
                ]);
            }

            // Auditoría con diff JSON (RNF-OTR-01: trazabilidad de cambios)
            Auditoria::registrar(
                $request->user()->id_usuario,
                'ALTA_MEDICO',
                'medicos',
                $medico->id_medico,
                ['nuevo' => $medico->toArray()]
            );

            DB::commit();

            // ── E-01 del CU-05: envío de credenciales (PLACEHOLDER) ─────
            // Decisión acordada contigo: el Mailable real se conecta en la
            // Fase 5 (CU-09). Por ahora se registra en el log de Laravel
            // para poder verificar el flujo completo sin depender todavía
            // del servicio de correo.
            Mail::to($usuario->correo_electronico)
                ->queue(new CredencialesMedicoMail($usuario, $passwordTemporal, $tokenActivacion));


            return redirect()->route('admin.medicos.index')
                ->with('exito', 'Médico registrado exitosamente. (Envío de credenciales pendiente de Fase 5 — revisar log)');
        } catch (\Exception $e) {
            DB::rollBack();
            // No se expone el detalle técnico al usuario final (buena práctica de seguridad)
            report($e);
            return back()->withInput()->with('error', 'Error de conexión. Intente nuevamente en unos minutos.');
        }
    }

    /**
     * CU-05 — Desactivar médico, validando primero que no tenga citas
     * futuras pendientes (flujo alternativo 5a del SRS).
     */
    public function desactivar(Medico $medico)
    {
        // Se cuentan las citas futuras ANTES de tocar nada — si hay alguna,
        // se aborta la operación completa (no se llega ni a abrir transacción).
        $citasFuturas = $medico->citas()
            ->whereIn('estado', ['Pendiente_Confirmacion', 'Confirmada'])
            ->where('fecha_cita', '>=', now()->toDateString())
            ->count();

        if ($citasFuturas > 0) {
            return back()->with(
                'error',
                "No se puede desactivar al médico. Existen {$citasFuturas} citas futuras que deben ser reasignadas o canceladas primero."
            );
        }

        // Se guarda el valor anterior para el diff de auditoría (antes vs. después)
        $anterior = $medico->only(['estado']);
        $medico->update(['estado' => 'Inactivo']);

        Auditoria::registrar(
            auth()->id(),
            'DESACTIVAR_MEDICO',
            'medicos',
            $medico->id_medico,
            [
                'anterior' => $anterior,
                'nuevo' => ['estado' => 'Inactivo'],
            ]
        );

        return back()->with('exito', 'Médico desactivado correctamente.');
    }
}
