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
        Schema::table('periodo_liquidacion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_empresa')->nullable()->after('id_periodo');
            $table->string('tipo_frecuencia')->nullable()->after('fecha_fin');

            $table->foreign('id_empresa')->references('id_empresa')->on('empresa');

            // Unique constraint to prevent overlapping periods of the same type for the same company
            $table->unique(['id_empresa', 'fecha_inicio', 'tipo_frecuencia'], 'unique_periodo_empresa_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodo_liquidacion', function (Blueprint $table) {
            $table->dropUnique('unique_periodo_empresa_inicio');
            $table->dropForeign(['id_empresa']);
            $table->dropColumn(['id_empresa', 'tipo_frecuencia']);
        });
    }
};
