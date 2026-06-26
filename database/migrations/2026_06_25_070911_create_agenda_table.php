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
        Schema::create('agenda', function (Blueprint $table) {
            $table->id('id_agenda');
            $table->foreignId('id_medico')->constrained('medicos', 'id_medico')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_sede')->constrained('sedes', 'id_sede')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('tipo', ['Regular', 'Excepcion'])->default('Regular');
            $table->tinyInteger('dia_semana')->nullable()
                  ->comment('1=Lunes … 7=Domingo; NULL si es excepción por fecha');
            $table->date('fecha_especifica')->nullable();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->tinyInteger('duracion_minutos')->default(30);
            $table->enum('estado', ['Disponible', 'Reservado', 'Bloqueado', 'En_Proceso_Reasignacion'])
                  ->default('Disponible');
            $table->string('motivo_bloqueo', 255)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        
            $table->index(['id_medico', 'fecha_especifica', 'estado']);
            $table->index(['id_medico', 'dia_semana', 'estado']);
            $table->index(['id_sede', 'estado']);
        });
        
        DB::statement('ALTER TABLE agenda ADD CONSTRAINT chk_ag_hora CHECK (hora_fin > hora_inicio)');
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
