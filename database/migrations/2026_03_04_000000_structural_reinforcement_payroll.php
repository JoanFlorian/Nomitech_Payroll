<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar restricción UNIQUE en tabla salario para ['id_contrato', 'id_periodo']
        Schema::table('salario', function (Blueprint $table) {
            $table->unique(['id_contrato', 'id_periodo'], 'unique_contrato_periodo');
        });

        // 2. Modificar tabla periodo_liquidacion: Agregar campo 'estado' y su índice
        Schema::table('periodo_liquidacion', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->after('fecha_fin');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodo_liquidacion', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropColumn('estado');
        });

        Schema::table('salario', function (Blueprint $table) {
            $table->dropUnique('unique_contrato_periodo');
        });
    }
};
