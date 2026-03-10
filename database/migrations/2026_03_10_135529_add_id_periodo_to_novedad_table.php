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
            if (!Schema::hasColumn('novedad', 'id_periodo')) {
                $table->integer('id_periodo')->nullable()->after('id_salario');
                $table->foreign('id_periodo')->references('id_periodo')->on('periodo_liquidacion')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            if (Schema::hasColumn('novedad', 'id_periodo')) {
                $table->dropForeign(['id_periodo']);
                $table->dropColumn('id_periodo');
            }
        });
    }
};
