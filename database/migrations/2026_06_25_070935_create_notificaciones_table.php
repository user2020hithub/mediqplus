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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->foreignId('id_cita')->constrained('citas', 'id_cita')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('tipo_notificacion', [
                'Confirmacion', 'Recordatorio_24h', 'Oferta_Reasignacion', 'Cancelacion', 'Contingencia',
            ]);
            $table->string('destinatario_email', 100);
            $table->enum('estado', ['Pendiente', 'Enviado', 'Fallido', 'Rebotado'])->default('Pendiente');
            $table->tinyInteger('intentos')->default(0);
            $table->dateTime('fecha_programada');
            $table->dateTime('fecha_envio')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->enum('prioridad', ['Alta', 'Normal'])->default('Normal');
            $table->timestamp('fecha_creacion')->useCurrent();
        
            $table->index(['id_cita', 'estado']);
            $table->index(['fecha_programada', 'estado']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
