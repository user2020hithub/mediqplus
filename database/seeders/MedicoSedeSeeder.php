<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicoSede;

class MedicoSedeSeeder extends Seeder
{
    public function run(): void
    {
        MedicoSede::insert([
            // García (1) atiende en San Isidro (1) y Miraflores (2)
            ['id_medico' => 1, 'id_sede' => 1, 'activo' => true],
            ['id_medico' => 1, 'id_sede' => 2, 'activo' => true],

            // Torres (2) en San Isidro (1) y Surco (3)
            ['id_medico' => 2, 'id_sede' => 1, 'activo' => true],
            ['id_medico' => 2, 'id_sede' => 3, 'activo' => true],

            // Mendoza (3) solo en Miraflores (2)
            ['id_medico' => 3, 'id_sede' => 2, 'activo' => true],

            // Quispe (4) en Surco (3) y La Molina (4)
            ['id_medico' => 4, 'id_sede' => 3, 'activo' => true],
            ['id_medico' => 4, 'id_sede' => 4, 'activo' => true],

            // Vargas (5) solo en San Isidro (1)
            ['id_medico' => 5, 'id_sede' => 1, 'activo' => true],
        ]);
    }
}
