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
        Schema::table('salario', function (Blueprint $table) {
            if (!Schema::hasColumn('salario', 'total_novedades_devengado')) {
                $table->decimal('total_novedades_devengado', 15, 2)->nullable()->after('total_deducciones')
                    ->comment('Total de devengos originados en novedades (incluye vacaciones y licencias).');
            }
            if (!Schema::hasColumn('salario', 'total_novedades_deduccion')) {
                $table->decimal('total_novedades_deduccion', 15, 2)->nullable()->after('total_novedades_devengado')
                    ->comment('Total de deducciones originadas en novedades.');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->dropColumn(['total_novedades_devengado', 'total_novedades_deduccion']);
        });
    }
};
