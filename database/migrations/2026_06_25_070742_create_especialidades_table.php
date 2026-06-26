<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id('id_especialidad');
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->tinyInteger('duracion_default')->default(30)
                  ->comment('Duración en minutos: 15, 20, 30, 45 o 60');
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->index('estado'); // Útil para listados activos
        });

        // Constraint CHECK (Laravel no lo genera automático en MySQL)
        DB::statement('ALTER TABLE especialidades ADD CONSTRAINT chk_esp_duracion
            CHECK (duracion_default IN (15, 20, 30, 45, 60))');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
