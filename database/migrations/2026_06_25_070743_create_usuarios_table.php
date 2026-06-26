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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->enum('rol', ['paciente', 'admin', 'medico']);
            $table->string('correo_electronico', 100)->unique();
            $table->string('password_hash', 255)->comment('bcrypt, costo >= 12');
            $table->enum('estado_cuenta', ['Pendiente_Verificacion', 'Activo', 'Suspendido'])
                  ->default('Pendiente_Verificacion');
            $table->tinyInteger('intentos_fallidos')->default(0);
            $table->dateTime('bloqueado_hasta')->nullable();
            $table->dateTime('ultimo_login')->nullable();
        
            // ── 2FA (RF-11) ─────────────────────────────
            $table->string('totp_secret', 255)->nullable()
                  ->comment('Clave TOTP cifrada con AES-256');
            $table->boolean('habilitado_2fa')->default(false);
            $table->tinyInteger('intentos_fallidos_2fa')->default(0);
            $table->dateTime('bloqueado_2fa_hasta')->nullable();
            $table->string('codigo_respaldo', 6)->nullable();
            $table->boolean('codigo_respaldo_usado')->default(true);
            $table->dateTime('codigo_respaldo_expira')->nullable();
        
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();
        
            $table->index('correo_electronico');
            $table->index('estado_cuenta');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
