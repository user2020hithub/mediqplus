<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sede;

class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sede::insert([
            [
                'nombre' => 'TuSalud San Isidro',
                'direccion' => 'Av. Javier Prado Oeste 1234',
                'distrito' => 'San Isidro',
                'telefono' => '016123456',
                'estado' => 'Activo'
            ],
            [
                'nombre' => 'TuSalud Miraflores',
                'direccion' => 'Av. Larco 567, Piso 2',
                'distrito' => 'Miraflores',
                'telefono' => '016789012',
                'estado' => 'Activo'
            ],
            [
                'nombre' => 'TuSalud Surco',
                'direccion' => 'Av. Primavera 2890',
                'distrito' => 'Santiago de Surco',
                'telefono' => '016345678',
                'estado' => 'Activo'
            ],
            [
                'nombre' => 'TuSalud La Molina',
                'direccion' => 'Av. La Fontana 1450',
                'distrito' => 'La Molina',
                'telefono' => '016901234',
                'estado' => 'Activo'
            ],
        ]);
    }
}
