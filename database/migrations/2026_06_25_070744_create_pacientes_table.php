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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id('id_paciente');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->string('tipo_documento', 10)->comment('DNI | CE | CPP');
            $table->string('numero_documento', 12);
            $table->string('nombres_apellidos', 100);
            $table->date('fecha_nacimiento')->comment('Para validar mayoría de edad (>=18)');
            $table->enum('sexo', ['Masculino', 'Femenino']);
            $table->string('telefono_movil', 9)->nullable();
            $table->enum('estado_correo', ['Activo', 'Invalido', 'Rebotado'])->default('Activo');
            $table->boolean('acepta_privacidad')->default(false);
            $table->string('token_activacion', 64)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        
            $table->unique(['tipo_documento', 'numero_documento']);
            $table->index('id_usuario');
            $table->index('numero_documento');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
