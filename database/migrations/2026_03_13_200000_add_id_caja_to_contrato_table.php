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
        Schema::table('contrato', function (Blueprint $table) {
            // Agregar campo id_caja después de id_afp
            $table->unsignedBigInteger('id_caja')->nullable()->after('id_afp');
            
            // Agregar foreign key
            $table->foreign('id_caja')
                  ->references('id_caja')
                  ->on('cajas_compensacion')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato', function (Blueprint $table) {
            $table->dropForeign(['id_caja']);
            $table->dropColumn('id_caja');
        });
    }
};
