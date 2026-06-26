<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medico;

class MedicoSeeder extends Seeder
{
    public function run(): void
    {
        Medico::insert([
            [
                'id_usuario' => 3,
                'id_especialidad' => 1,
                'nombres_apellidos' => 'Luis Alberto García Paredes',
                'dni' => '45678901',
                'colegiatura' => 'CMP-054321',
                'estado' => 'Activo'
            ],

            [
                'id_usuario' => 4,
                'id_especialidad' => 2,
                'nombres_apellidos' => 'Rosa Elena Torres Villanueva',
                'dni' => '32156789',
                'colegiatura' => 'CMP-067890',
                'estado' => 'Activo'
            ],

            [
                'id_usuario' => 5,
                'id_especialidad' => 4,
                'nombres_apellidos' => 'Ramiro Mendoza Ccallo',
                'dni' => '28934567',
                'colegiatura' => 'CMP-041235',
                'estado' => 'Activo'
            ],

            [
                'id_usuario' => 6,
                'id_especialidad' => 6,
                'nombres_apellidos' => 'Ana María Quispe Huanca',
                'dni' => '51234567',
                'colegiatura' => 'CMP-078901',
                'estado' => 'Activo'
            ],

            [
                'id_usuario' => 7,
                'id_especialidad' => 7,
                'nombres_apellidos' => 'Dante Vargas Soto',
                'dni' => '39012345',
                'colegiatura' => 'CMP-034567',
                'estado' => 'Activo'
            ],
        ]);
    }
}
