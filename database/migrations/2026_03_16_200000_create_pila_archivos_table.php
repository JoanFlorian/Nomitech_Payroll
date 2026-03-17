<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pila_archivos', function (Blueprint $table) {
            $table->id();
            $table->integer('periodo_id');
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->unsignedInteger('total_empleados')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['empresa_id', 'periodo_id'], 'idx_pila_archivos_empresa_periodo');
            $table->foreign('periodo_id')->references('id_periodo')->on('periodo_liquidacion');
            $table->foreign('empresa_id')->references('id_empresa')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pila_archivos');
    }
};
