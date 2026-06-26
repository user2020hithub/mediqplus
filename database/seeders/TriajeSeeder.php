<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Triaje;

class TriajeSeeder extends Seeder
{
    public function run(): void
    {
        // El Model Triaje cifra detalle_alergias y medicamentos_actuales
        // automáticamente vía mutators — aquí se pasan en texto plano.
        Triaje::create([
            'id_cita' => 3,
            'motivo_consulta' => 'Presento dolor de cabeza pulsátil en zona frontal desde hace 3 días, empeora con la luz.',
            'sintomas' => ['cefalea_pulsatil', 'fotofobia', 'nauseas'],
            'intensidad' => 7,
            'tiene_alergias' => true,
            'detalle_alergias' => 'Penicilina, Ibuprofeno',
            'medicamentos_actuales' => 'Paracetamol 500mg c/8h (automedicación)',
            'acepta_disclaimer' => true,
        ]);
    }
}
