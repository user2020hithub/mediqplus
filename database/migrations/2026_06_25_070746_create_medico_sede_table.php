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
        Schema::create('medico_sede', function (Blueprint $table) {
            $table->id('id_medico_sede');
            $table->foreignId('id_medico')->constrained('medicos', 'id_medico')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('id_sede')->constrained('sedes', 'id_sede')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('activo')->default(true);
        
            $table->unique(['id_medico', 'id_sede']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medico_sede');
    }
};
