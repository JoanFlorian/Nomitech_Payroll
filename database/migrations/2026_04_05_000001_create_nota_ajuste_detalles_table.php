<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_ajuste_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_id');
            $table->string('campo', 100);
            $table->decimal('valor_original', 12, 2)->default(0);
            $table->decimal('valor_corregido', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->boolean('es_salarial')->default(true);
            $table->string('guardado_por', 50)->nullable();
            $table->string('aprobado_por', 50)->nullable();
            $table->timestamp('aprobado_en')->nullable();
            $table->timestamps();

            $table->foreign('nota_id')
                ->references('id')
                ->on('nota_ajustes')
                ->onDelete('cascade');

            $table->unique(['nota_id', 'campo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_ajuste_detalles');
    }
};
