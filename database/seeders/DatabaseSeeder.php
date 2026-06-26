<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EspecialidadSeeder::class,   // Bloque 1 · 10 filas (con descripcion)
            SedeSeeder::class,           // Bloque 1 · 4 filas
            UsuarioSeeder::class,        // Bloque 2 · 12 filas
            MedicoSeeder::class,         // Bloque 2 · 5 filas
            PacienteSeeder::class,       // Bloque 2 · 5 filas
            MedicoSedeSeeder::class,     // Bloque 2 · 8 filas
            AgendaSeeder::class,         // Bloque 3 · 13 filas ← AGREGADO
            CitaSeeder::class,           // Bloque 4 · 6 filas
            TriajeSeeder::class,         // Bloque 5 · 1 fila
            ListaEsperaSeeder::class,    // Bloque 6 · 3 filas
        ]);
    }
}
