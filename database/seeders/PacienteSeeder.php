<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paciente;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        Paciente::insert([
            ['id_usuario' => 8, 'tipo_documento' => 'DNI', 'numero_documento' => '72345678',
             'nombres_apellidos' => 'Pérez Sánchez Juan Manuel',
             'fecha_nacimiento' => '1990-04-15', 'sexo' => 'Masculino',
             'telefono_movil' => '987654321', 'acepta_privacidad' => true],

            ['id_usuario' => 9, 'tipo_documento' => 'DNI', 'numero_documento' => '65432109',
             'nombres_apellidos' => 'Flores Ríos María del Carmen',
             'fecha_nacimiento' => '1985-09-22', 'sexo' => 'Femenino',
             'telefono_movil' => '976543210', 'acepta_privacidad' => true],

            ['id_usuario' => 10, 'tipo_documento' => 'DNI', 'numero_documento' => '58765432',
             'nombres_apellidos' => 'Ramos Huamán Carlos Augusto',
             'fecha_nacimiento' => '1978-12-01', 'sexo' => 'Masculino',
             'telefono_movil' => '965432109', 'acepta_privacidad' => true],

            ['id_usuario' => 11, 'tipo_documento' => 'DNI', 'numero_documento' => '81234567',
             'nombres_apellidos' => 'Méndez Pachas Lucía Patricia',
             'fecha_nacimiento' => '1995-06-30', 'sexo' => 'Femenino',
             'telefono_movil' => '954321098', 'acepta_privacidad' => true],

            ['id_usuario' => 12, 'tipo_documento' => 'DNI', 'numero_documento' => '90123456',
             'nombres_apellidos' => 'Castillo Cano Pedro Enrique',
             'fecha_nacimiento' => '2000-02-18', 'sexo' => 'Masculino',
             'telefono_movil' => null, 'acepta_privacidad' => true],
        ]);
    }
}
