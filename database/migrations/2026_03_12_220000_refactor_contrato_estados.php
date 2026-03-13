<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contrato', function (Blueprint $table) {
            // New unified status field
            $table->string('estado', 30)->default('ACTIVO')->after('activo');

            // Liquidation tracking timestamps
            $table->dateTime('salario_final_pagado_at')->nullable()->after('fecha_liquidacion_final');
            $table->dateTime('prestaciones_liquidadas_at')->nullable()->after('salario_final_pagado_at');
            $table->dateTime('cesantias_transferidas_at')->nullable()->after('prestaciones_liquidadas_at');
            $table->dateTime('vacaciones_liquidadas_at')->nullable()->after('cesantias_transferidas_at');
        });

        // ── Migrate existing data ──
        // estado_laboral=1 (ACTIVO) → 'ACTIVO'
        // estado_laboral=2 (TERMINADO) + estado_nomina=2 (LIQUIDADO) → 'TERMINADO'
        // estado_laboral=2 (TERMINADO) + estado_nomina=1 (PENDIENTE) → 'VENCIDO'
        DB::table('contrato')
            ->where('estado_laboral', 1)
            ->update(['estado' => 'ACTIVO']);

        DB::table('contrato')
            ->where('estado_laboral', 2)
            ->where('estado_nomina', 2)
            ->update([
                'estado' => 'TERMINADO',
                'salario_final_pagado_at' => DB::raw('fecha_liquidacion_final'),
                'prestaciones_liquidadas_at' => DB::raw('fecha_liquidacion_final'),
                'cesantias_transferidas_at' => DB::raw('fecha_liquidacion_final'),
                'vacaciones_liquidadas_at' => DB::raw('fecha_liquidacion_final'),
            ]);

        DB::table('contrato')
            ->where('estado_laboral', 2)
            ->where('estado_nomina', 1)
            ->update(['estado' => 'VENCIDO']);

        // ── Drop old columns ──
        Schema::table('contrato', function (Blueprint $table) {
            $table->dropColumn(['estado_laboral', 'estado_nomina']);
        });
    }

    public function down(): void
    {
        Schema::table('contrato', function (Blueprint $table) {
            // Restore old columns
            $table->unsignedTinyInteger('estado_laboral')->default(1)->after('activo');
            $table->unsignedTinyInteger('estado_nomina')->default(1)->after('estado_laboral');
        });

        // Reverse mapping
        DB::table('contrato')
            ->whereIn('estado', ['ACTIVO', 'POR_VENCER', 'PROGRAMADO'])
            ->update(['estado_laboral' => 1, 'estado_nomina' => 1]);

        DB::table('contrato')
            ->where('estado', 'VENCIDO')
            ->update(['estado_laboral' => 2, 'estado_nomina' => 1]);

        DB::table('contrato')
            ->where('estado', 'TERMINADO')
            ->update(['estado_laboral' => 2, 'estado_nomina' => 2]);

        Schema::table('contrato', function (Blueprint $table) {
            $table->dropColumn([
                'estado',
                'salario_final_pagado_at',
                'prestaciones_liquidadas_at',
                'cesantias_transferidas_at',
                'vacaciones_liquidadas_at',
            ]);
        });
    }
};
