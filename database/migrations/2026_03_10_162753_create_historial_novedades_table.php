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
        Schema::create('historial_novedades', function (Blueprint $table) {
            $table->id('id_historial_novedad');
            $table->unsignedInteger('id_novedad')->nullable();
            $table->unsignedBigInteger('id_salario')->nullable();
            $table->string('empleado_id', 20);
            $table->string('tipo_novedad', 100);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('valor', 15, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('accion', 50); // 'crear', 'actualizar', 'eliminar'
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('usuario_nombre', 255)->nullable();
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('id_novedad');
            $table->index('empleado_id');
            $table->index('tipo_novedad');
            $table->index('accion');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_novedades');
    }
};
