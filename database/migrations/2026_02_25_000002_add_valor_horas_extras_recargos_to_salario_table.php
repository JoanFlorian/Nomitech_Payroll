<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->decimal('valor_horas_extras_recargos', 12, 2)
                ->default(0)
                ->after('horas_extra');
        });
    }

    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->dropColumn('valor_horas_extras_recargos');
        });
    }
};
