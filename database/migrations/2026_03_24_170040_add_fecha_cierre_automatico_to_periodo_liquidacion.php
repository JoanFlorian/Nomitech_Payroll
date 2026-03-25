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
        Schema::table('periodo_liquidacion', function (Blueprint $table) {
            $table->date('fecha_cierre_automatico')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodo_liquidacion', function (Blueprint $table) {
            $table->dropColumn('fecha_cierre_automatico');
        });
    }
};
