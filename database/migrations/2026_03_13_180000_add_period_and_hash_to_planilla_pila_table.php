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
        Schema::table('planilla_pila', function (Blueprint $table) {
            if (!Schema::hasColumn('planilla_pila', 'id_periodo')) {
                $table->integer('id_periodo')->nullable()->after('id_empresa');
                $table->foreign('id_periodo')->references('id_periodo')->on('periodo_liquidacion')->onDelete('set null');
            }

            if (!Schema::hasColumn('planilla_pila', 'datos_hash')) {
                $table->string('datos_hash', 64)->nullable()->after('archivo_generado');
            }
        });

        Schema::table('planilla_pila', function (Blueprint $table) {
            $table->unique(['id_empresa', 'id_periodo'], 'planilla_pila_empresa_periodo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planilla_pila', function (Blueprint $table) {
            $table->dropUnique('planilla_pila_empresa_periodo_unique');

            if (Schema::hasColumn('planilla_pila', 'id_periodo')) {
                $table->dropForeign(['id_periodo']);
                $table->dropColumn('id_periodo');
            }

            if (Schema::hasColumn('planilla_pila', 'datos_hash')) {
                $table->dropColumn('datos_hash');
            }
        });
    }
};
