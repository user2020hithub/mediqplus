<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Agenda;

class AgendaSeeder extends Seeder
{
    public function run(): void
    {
        Agenda::insert([
            // ── García (id_medico=1, Medicina General) ──────────────
            // Mañana en San Isidro (id_sede=1): Lunes a Viernes
            [
                'id_medico' => 1,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 1,
                'hora_inicio' => '08:00:00',
                'hora_fin' => '13:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 1,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 2,
                'hora_inicio' => '08:00:00',
                'hora_fin' => '13:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 1,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 3,
                'hora_inicio' => '08:00:00',
                'hora_fin' => '13:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 1,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 4,
                'hora_inicio' => '08:00:00',
                'hora_fin' => '13:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 1,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 5,
                'hora_inicio' => '08:00:00',
                'hora_fin' => '13:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],

            // Tarde en Miraflores (id_sede=2): Lunes, Miércoles, Viernes
            [
                'id_medico' => 1,
                'id_sede' => 2,
                'tipo' => 'Regular',
                'dia_semana' => 1,
                'hora_inicio' => '15:00:00',
                'hora_fin' => '18:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 1,
                'id_sede' => 2,
                'tipo' => 'Regular',
                'dia_semana' => 3,
                'hora_inicio' => '15:00:00',
                'hora_fin' => '18:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 1,
                'id_sede' => 2,
                'tipo' => 'Regular',
                'dia_semana' => 5,
                'hora_inicio' => '15:00:00',
                'hora_fin' => '18:00:00',
                'duracion_minutos' => 20,
                'estado' => 'Disponible'
            ],

            // ── Torres (id_medico=2, Cardiología) ───────────────────
            // Martes y Jueves en San Isidro (id_sede=1)
            [
                'id_medico' => 2,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 2,
                'hora_inicio' => '09:00:00',
                'hora_fin' => '14:00:00',
                'duracion_minutos' => 30,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 2,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 4,
                'hora_inicio' => '09:00:00',
                'hora_fin' => '14:00:00',
                'duracion_minutos' => 30,
                'estado' => 'Disponible'
            ],

            // Lunes y Viernes en Surco (id_sede=3)
            [
                'id_medico' => 2,
                'id_sede' => 3,
                'tipo' => 'Regular',
                'dia_semana' => 1,
                'hora_inicio' => '15:00:00',
                'hora_fin' => '19:00:00',
                'duracion_minutos' => 30,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 2,
                'id_sede' => 3,
                'tipo' => 'Regular',
                'dia_semana' => 5,
                'hora_inicio' => '15:00:00',
                'hora_fin' => '19:00:00',
                'duracion_minutos' => 30,
                'estado' => 'Disponible'
            ],

            // ── Vargas (id_medico=5, Neurología) ────────────────────
            // Lunes a Jueves en San Isidro (id_sede=1)
            [
                'id_medico' => 5,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 1,
                'hora_inicio' => '10:00:00',
                'hora_fin' => '16:00:00',
                'duracion_minutos' => 45,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 5,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 2,
                'hora_inicio' => '10:00:00',
                'hora_fin' => '16:00:00',
                'duracion_minutos' => 45,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 5,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 3,
                'hora_inicio' => '10:00:00',
                'hora_fin' => '16:00:00',
                'duracion_minutos' => 45,
                'estado' => 'Disponible'
            ],
            [
                'id_medico' => 5,
                'id_sede' => 1,
                'tipo' => 'Regular',
                'dia_semana' => 4,
                'hora_inicio' => '10:00:00',
                'hora_fin' => '16:00:00',
                'duracion_minutos' => 45,
                'estado' => 'Disponible'
            ],
        ]);
    }
}
