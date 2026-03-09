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
            if (!Schema::hasColumn('salario', 'caja_compensacion')) {
                $table->decimal('caja_compensacion', 12, 2)->default(0)->after('pension_voluntaria');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            if (Schema::hasColumn('salario', 'caja_compensacion')) {
                $table->dropColumn('caja_compensacion');
            }
        });
    }
};
