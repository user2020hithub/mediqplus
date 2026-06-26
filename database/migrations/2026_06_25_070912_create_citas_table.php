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
        Schema::create('citas', function (Blueprint $table) {
            $table->id('id_cita');
            $table->string('codigo_cita', 20)->unique()->comment('Ej.: CITA-20250615-0042');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_medico')->constrained('medicos', 'id_medico')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_sede')->constrained('sedes', 'id_sede')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_agenda')->constrained('agenda', 'id_agenda')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->date('fecha_cita');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', [
                'Pendiente_Confirmacion', 'Confirmada', 'Cancelada', 'Atendida',
                'No_Show', 'Pendiente_Reprogramacion', 'Triaje_Completado',
            ])->default('Pendiente_Confirmacion');
            $table->string('motivo_consulta', 255)->nullable();
        
            // Cancelación
            $table->string('motivo_cancelacion', 100)->nullable();
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->boolean('es_cancelacion_tardia')->default(false);
            $table->foreignId('usuario_cancelacion')->nullable()
                  ->constrained('usuarios', 'id_usuario')->nullOnDelete()->cascadeOnUpdate();
        
            // Reprogramación masiva (RF-12)
            $table->string('token_reprogramacion', 64)->nullable()->unique();
        
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();
        
            $table->index(['id_paciente', 'fecha_cita', 'estado']);
            $table->index(['id_medico', 'fecha_cita', 'estado']);
            $table->index(['fecha_cita', 'estado']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
