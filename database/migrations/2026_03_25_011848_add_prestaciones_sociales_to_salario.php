<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->decimal('prestaciones_sociales', 15, 2)->default(0)->after('neto_pagar')
                ->comment('Monto total de prestaciones sociales integradas (prima, cesantías, intereses, vacaciones)');
        });
    }

    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->dropColumn('prestaciones_sociales');
        });
    }
};
