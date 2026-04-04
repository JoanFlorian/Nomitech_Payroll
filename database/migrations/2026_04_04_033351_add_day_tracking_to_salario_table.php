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
            if (!Schema::hasColumn('salario', 'dias_trabajados')) {
                $table->integer('dias_trabajados')->nullable()->after('dias_a_trabajar')
                    ->comment('Días efectivos pagados como sueldo (Neto).');
            }
            if (!Schema::hasColumn('salario', 'dias_ausencia')) {
                $table->integer('dias_ausencia')->nullable()->after('dias_trabajados')
                    ->comment('Total días de ausencia restados (Diferencia).');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->dropColumn(['dias_trabajados', 'dias_ausencia']);
        });
    }
};
