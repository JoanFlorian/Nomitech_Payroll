<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pila_detalle_empleado')) {
            return;
        }

        Schema::table('pila_detalle_empleado', function (Blueprint $table) {
            if (!Schema::hasColumn('pila_detalle_empleado', 'ibc_caja')) {
                $table->decimal('ibc_caja', 14, 2)->default(0)->after('ibc_arl');
            }
            if (!Schema::hasColumn('pila_detalle_empleado', 'nivel_riesgo_arl')) {
                $table->tinyInteger('nivel_riesgo_arl')->default(1)->after('ibc_caja');
            }
            if (!Schema::hasColumn('pila_detalle_empleado', 'valor_arl')) {
                $table->decimal('valor_arl', 14, 2)->default(0)->after('nivel_riesgo_arl');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pila_detalle_empleado')) {
            return;
        }

        Schema::table('pila_detalle_empleado', function (Blueprint $table) {
            $cols = ['ibc_caja', 'nivel_riesgo_arl', 'valor_arl'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pila_detalle_empleado', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
