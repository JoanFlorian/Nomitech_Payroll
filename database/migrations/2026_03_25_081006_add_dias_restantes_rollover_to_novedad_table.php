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
        Schema::table('novedad', function (Blueprint $table) {
            // Almacenar días restantes de licencias que continúan en siguiente período
            if (!Schema::hasColumn('novedad', 'dias_restantes_rollover')) {
                $table->integer('dias_restantes_rollover')->nullable()->default(null)->after('dias')
                    ->comment('Días restantes de maternidad/paternidad para continuar en siguiente período');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            if (Schema::hasColumn('novedad', 'dias_restantes_rollover')) {
                $table->dropColumn('dias_restantes_rollover');
            }
        });
    }
};
