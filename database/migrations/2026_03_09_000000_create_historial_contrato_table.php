<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_contrato', function (Blueprint $table) {
            $table->unsignedBigInteger('id_historial')->autoIncrement();
            $table->unsignedBigInteger('id_contrato');

            $table->string('dato_anterior', 191);
            $table->string('dato_nuevo', 191);
            $table->enum('tipo_novedad', ['EPS', 'AFP', 'SALARIO']);
            $table->dateTime('fecha_cambio')->useCurrent();

            $table->foreign('id_contrato')
                ->references('id_contrato')
                ->on('contrato')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_contrato');
    }
};
