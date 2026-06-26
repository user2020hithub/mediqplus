<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::insert([
            // ── Administradores (id_usuario 1–2) ─────────────
            [
                'rol' => 'admin',
                'correo_electronico' => 'admin@tusalud.pe',
                'password_hash' => Hash::make('Admin2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            [
                'rol' => 'admin',
                'correo_electronico' => 'operaciones@tusalud.pe',
                'password_hash' => Hash::make('Admin2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            // ── Médicos (id_usuario 3–7) ──────────────────────
            [
                'rol' => 'medico',
                'correo_electronico' => 'dr.garcia@tusalud.pe',
                'password_hash' => Hash::make('Medic2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            [
                'rol' => 'medico',
                'correo_electronico' => 'dra.torres@tusalud.pe',
                'password_hash' => Hash::make('Medic2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            [
                'rol' => 'medico',
                'correo_electronico' => 'dr.mendoza@tusalud.pe',
                'password_hash' => Hash::make('Medic2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            [
                'rol' => 'medico',
                'correo_electronico' => 'dra.quispe@tusalud.pe',
                'password_hash' => Hash::make('Medic2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            [
                'rol' => 'medico',
                'correo_electronico' => 'dr.vargas@tusalud.pe',
                'password_hash' => Hash::make('Medic2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => true
            ],

            // ── Pacientes (id_usuario 8–12) ───────────────────
            [
                'rol' => 'paciente',
                'correo_electronico' => 'juan.perez@gmail.com',
                'password_hash' => Hash::make('Paciente2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => false
            ],

            [
                'rol' => 'paciente',
                'correo_electronico' => 'maria.flores@gmail.com',
                'password_hash' => Hash::make('Paciente2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => false
            ],

            [
                'rol' => 'paciente',
                'correo_electronico' => 'carlos.ramos@hotmail.com',
                'password_hash' => Hash::make('Paciente2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => false
            ],

            [
                'rol' => 'paciente',
                'correo_electronico' => 'lucia.mendez@gmail.com',
                'password_hash' => Hash::make('Paciente2025!'),
                'estado_cuenta' => 'Activo',
                'habilitado_2fa' => false
            ],

            [
                'rol' => 'paciente',
                'correo_electronico' => 'pedro.castillo@yahoo.com',
                'password_hash' => Hash::make('Paciente2025!'),
                'estado_cuenta' => 'Pendiente_Verificacion',
                'habilitado_2fa' => false
            ],
        ]);
    }
}
