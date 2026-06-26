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
        Schema::create('lista_espera', function (Blueprint $table) {
            $table->id('id_lista');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_especialidad')->constrained('especialidades', 'id_especialidad')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_sede')->constrained('sedes', 'id_sede')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->date('fecha_inicio_pref')->nullable();
            $table->date('fecha_fin_pref')->nullable();
            $table->enum('estado', ['Activa', 'Completada', 'Expirada', 'Cancelada'])->default('Activa');
            $table->boolean('consentimiento')->default(false);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->dateTime('fecha_vencimiento');
        
            $table->index(['id_especialidad', 'id_sede', 'estado']);
            $table->index(['fecha_vencimiento', 'estado']);
            $table->index('id_paciente');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_espera');
    }
};
