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
        Schema::table('salario', function (Blueprint $table) {
            if (!Schema::hasColumn('salario', 'total_devengado')) {
                $table->decimal('total_devengado', 12, 2)->default(0)->after('dias_a_trabajar');
            }
            if (!Schema::hasColumn('salario', 'total_deducciones')) {
                $table->decimal('total_deducciones', 12, 2)->default(0)->after('total_devengado');
            }
            if (!Schema::hasColumn('salario', 'neto_pagar')) {
                $table->decimal('neto_pagar', 12, 2)->default(0)->after('total_deducciones');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            if (Schema::hasColumn('salario', 'neto_pagar')) {
                $table->dropColumn('neto_pagar');
            }
            if (Schema::hasColumn('salario', 'total_deducciones')) {
                $table->dropColumn('total_deducciones');
            }
            if (Schema::hasColumn('salario', 'total_devengado')) {
                $table->dropColumn('total_devengado');
            }
        });
    }
};
