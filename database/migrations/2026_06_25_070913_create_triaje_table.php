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
        Schema::create('triaje', function (Blueprint $table) {
            $table->id('id_triaje');
            $table->foreignId('id_cita')->unique()->constrained('citas', 'id_cita')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->string('motivo_consulta', 500);
            $table->json('sintomas')->comment('Array de IDs/strings del catálogo de síntomas');
            $table->tinyInteger('intensidad')->comment('Escala 1–10');
            $table->boolean('tiene_alergias')->default(false);
            $table->binary('detalle_alergias')->nullable()
                  ->comment('Cifrado AES-256 vía Crypt::encrypt() en el Model');
            $table->binary('medicamentos_actuales')->nullable()
                  ->comment('Cifrado AES-256 vía Crypt::encrypt() en el Model');
            $table->boolean('acepta_disclaimer')->default(false);
            $table->timestamp('fecha_creacion')->useCurrent();
        });
        
        DB::statement('ALTER TABLE triaje ADD CONSTRAINT chk_tri_intensidad
            CHECK (intensidad BETWEEN 1 AND 10)');
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triaje');
    }
};
