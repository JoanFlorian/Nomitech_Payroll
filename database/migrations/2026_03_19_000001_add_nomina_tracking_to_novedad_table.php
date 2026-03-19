<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            // Indica si esta novedad debe afectar el cálculo de nómina.
            // Útil para registros informativos que no generan devengos ni deducciones.
            $table->boolean('afecta_nomina')->default(true)->after('certificado_medico');

            // Periodo en el que esta novedad fue efectivamente aplicada en nómina.
            // Para IGE/IRL esto garantiza que solo afecten el periodo de registro.
            $table->integer('periodo_aplicado_id')->unsigned()->nullable()->after('afecta_nomina');

            $table->foreign('periodo_aplicado_id')
                ->references('id_periodo')
                ->on('periodo_liquidacion')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            $table->dropForeign(['periodo_aplicado_id']);
            $table->dropColumn(['afecta_nomina', 'periodo_aplicado_id']);
        });
    }
};
