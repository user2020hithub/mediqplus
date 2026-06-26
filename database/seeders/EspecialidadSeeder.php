<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        Especialidad::insert([
            [
                'nombre' => 'Medicina General',
                'descripcion' => 'Atención de salud general y preventiva',
                'duracion_default' => 20,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Cardiología',
                'descripcion' => 'Diagnóstico y tratamiento de enfermedades del corazón',
                'duracion_default' => 30,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Pediatría',
                'descripcion' => 'Atención médica para menores de 18 años',
                'duracion_default' => 20,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Dermatología',
                'descripcion' => 'Enfermedades de la piel, cabello y uñas',
                'duracion_default' => 20,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Traumatología',
                'descripcion' => 'Lesiones y enfermedades del aparato locomotor',
                'duracion_default' => 30,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Ginecología',
                'descripcion' => 'Salud reproductiva y del sistema femenino',
                'duracion_default' => 30,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Neurología',
                'descripcion' => 'Enfermedades del sistema nervioso',
                'duracion_default' => 45,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Oftalmología',
                'descripcion' => 'Salud visual y enfermedades oculares',
                'duracion_default' => 20,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Endocrinología',
                'descripcion' => 'Trastornos hormonales y metabólicos',
                'duracion_default' => 30,
                'estado' => 'Activo'
            ],

            [
                'nombre' => 'Psiquiatría',
                'descripcion' => 'Salud mental y trastornos psiquiátricos',
                'duracion_default' => 45,
                'estado' => 'Activo'
            ],
        ]);
    }
}
