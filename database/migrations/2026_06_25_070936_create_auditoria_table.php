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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id('id_auditoria')->startingValue(1); // BIGINT autoincremental
            $table->foreignId('id_usuario')->nullable()
                  ->constrained('usuarios', 'id_usuario')->nullOnDelete()->cascadeOnUpdate();
            $table->string('accion', 100);
            $table->string('tabla_afectada', 50)->nullable();
            $table->integer('id_registro')->nullable();
            $table->json('detalle_json')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->dateTime('timestamp_accion')->useCurrent();
        
            $table->index('id_usuario');
            $table->index('accion');
            $table->index('timestamp_accion');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
