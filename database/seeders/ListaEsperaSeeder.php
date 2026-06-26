<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ListaEspera;

class ListaEsperaSeeder extends Seeder
{
    public function run(): void
    {
        ListaEspera::insert([
            // Flores quiere Medicina General en San Isidro
            [
                'id_paciente' => 2,
                'id_especialidad' => 1,
                'id_sede' => 1,
                'fecha_inicio_pref' => '2025-06-20',
                'fecha_fin_pref' => '2025-07-20',
                'estado' => 'Activa',
                'consentimiento' => true,
                'fecha_vencimiento' => now()->addDays(30)
            ],

            // Castillo quiere Neurología en San Isidro (sin preferencia de fecha)
            [
                'id_paciente' => 5,
                'id_especialidad' => 7,
                'id_sede' => 1,
                'fecha_inicio_pref' => null,
                'fecha_fin_pref' => null,
                'estado' => 'Activa',
                'consentimiento' => true,
                'fecha_vencimiento' => now()->addDays(30)
            ],

            // Ramos ya fue atendido (suscripción completada)
            [
                'id_paciente' => 3,
                'id_especialidad' => 7,
                'id_sede' => 1,
                'fecha_inicio_pref' => null,
                'fecha_fin_pref' => null,
                'estado' => 'Completada',
                'consentimiento' => true,
                'fecha_vencimiento' => now()->addDays(30)
            ],
        ]);
    }
}
