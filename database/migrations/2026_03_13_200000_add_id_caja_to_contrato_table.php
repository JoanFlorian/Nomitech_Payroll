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
            // Only add the column if it doesn't already exist (handles partial migration)
            if (!Schema::hasColumn('contrato', 'id_caja')) {
                $table->unsignedBigInteger('id_caja')->nullable()->after('id_afp');
            }
        });

        // Add FK in a separate statement to isolate from column creation
        Schema::table('contrato', function (Blueprint $table) {
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
