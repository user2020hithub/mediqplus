<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cita;

class CitaSeeder extends Seeder
{
    public function run(): void
    {
        // Cita 1: Confirmada — García / Pérez
        Cita::create([
            'codigo_cita' => 'CITA-20250620-0001',
            'id_paciente' => 1,
            'id_medico' => 1,
            'id_sede' => 1,
            'id_agenda' => 1,
            'fecha_cita' => '2025-06-20',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '08:20:00',
            'estado' => 'Confirmada',
            'motivo_consulta' => 'Control rutinario de presión arterial',
        ]);

        // Cita 2: Pendiente confirmación — Torres / Flores
        Cita::create([
            'codigo_cita' => 'CITA-20250621-0002',
            'id_paciente' => 2,
            'id_medico' => 2,
            'id_sede' => 1,
            'id_agenda' => 9,
            'fecha_cita' => '2025-06-21',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '09:30:00',
            'estado' => 'Pendiente_Confirmacion',
            'motivo_consulta' => 'Evaluación cardíaca preventiva',
        ]);

        // Cita 3: Triaje completado — García / Ramos
        Cita::create([
            'codigo_cita' => 'CITA-20250619-0003',
            'id_paciente' => 3,
            'id_medico' => 1,
            'id_sede' => 1,
            'id_agenda' => 2,
            'fecha_cita' => '2025-06-19',
            'hora_inicio' => '08:20:00',
            'hora_fin' => '08:40:00',
            'estado' => 'Triaje_Completado',
            'motivo_consulta' => 'Dolor de cabeza recurrente',
        ]);

        // Cita 4: Atendida (histórico) — Vargas / Méndez
        Cita::create([
            'codigo_cita' => 'CITA-20250610-0004',
            'id_paciente' => 4,
            'id_medico' => 5,
            'id_sede' => 1,
            'id_agenda' => 13,
            'fecha_cita' => '2025-06-10',
            'hora_inicio' => '10:00:00',
            'hora_fin' => '10:45:00',
            'estado' => 'Atendida',
            'motivo_consulta' => 'Cefalea tensional y mareos',
        ]);

        // Cita 5: Cancelada tardía — García / Castillo
        Cita::create([
            'codigo_cita' => 'CITA-20250615-0005',
            'id_paciente' => 5,
            'id_medico' => 1,
            'id_sede' => 1,
            'id_agenda' => 3,
            'fecha_cita' => '2025-06-15',
            'hora_inicio' => '08:40:00',
            'hora_fin' => '09:00:00',
            'estado' => 'Cancelada',
            'motivo_cancelacion' => 'Cambio de horario',
            'fecha_cancelacion' => '2025-06-15 06:30:00',
            'es_cancelacion_tardia' => true,
            'usuario_cancelacion' => 12,
        ]);

        // Cita 6: Pendiente reprogramación (contingencia) — Torres / Pérez
        Cita::create([
            'codigo_cita' => 'CITA-20250625-0006',
            'id_paciente' => 1,
            'id_medico' => 2,
            'id_sede' => 1,
            'id_agenda' => 10,
            'fecha_cita' => '2025-06-25',
            'hora_inicio' => '09:30:00',
            'hora_fin' => '10:00:00',
            'estado' => 'Pendiente_Reprogramacion',
            'token_reprogramacion' => 'tkn_repr_abc123xyz789_0006',
        ]);
    }
}
