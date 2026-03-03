<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Contrato;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contrato', function (Blueprint $table) {
            $table->unsignedTinyInteger('estado_laboral')->default(Contrato::ESTADO_LABORAL_ACTIVO)->index()->after('activo');
            $table->unsignedTinyInteger('estado_nomina')->default(Contrato::ESTADO_NOMINA_PENDIENTE)->index()->after('estado_laboral');
            $table->dateTime('fecha_liquidacion_final')->nullable()->after('estado_nomina');
        });

        // Initialize existing data
        // 1: ACTIVO / PENDIENTE, 2: TERMINADO / LIQUIDADO
        DB::table('contrato')->where('activo', 1)->update([
            'estado_laboral' => Contrato::ESTADO_LABORAL_ACTIVO,
            'estado_nomina' => Contrato::ESTADO_NOMINA_PENDIENTE
        ]);

        DB::table('contrato')->where('activo', 0)->update([
            'estado_laboral' => Contrato::ESTADO_LABORAL_TERMINADO,
            'estado_nomina' => Contrato::ESTADO_NOMINA_LIQUIDADO
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato', function (Blueprint $table) {
            $table->dropColumn(['estado_laboral', 'estado_nomina', 'fecha_liquidacion_final']);
        });
    }
};
