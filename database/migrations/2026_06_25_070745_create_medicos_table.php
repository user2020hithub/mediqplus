<?php

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
        Schema::create('medicos', function (Blueprint $table) {
            $table->id('id_medico');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_especialidad')->constrained('especialidades', 'id_especialidad')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->string('nombres_apellidos', 100);
            $table->string('dni', 8)->unique()->comment('Almacenar cifrado AES-256');
            $table->string('colegiatura', 15)->unique();
            $table->string('telefono', 9)->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->string('token_activacion', 64)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        
            $table->index('id_usuario');
            $table->index('id_especialidad');
            $table->index('estado');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
