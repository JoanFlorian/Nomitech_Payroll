<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('contrato', 'nivel_riesgo_id')) {
            Schema::table('contrato', function (Blueprint $table) {
                $table->unsignedBigInteger('nivel_riesgo_id')->nullable()->after('nivel_riesgo');
                $table->index('nivel_riesgo_id', 'contrato_nivel_riesgo_id_idx');
            });
        }

        if (
            Schema::hasTable('niveles_riesgo')
            && Schema::hasColumn('contrato', 'nivel_riesgo')
            && Schema::hasColumn('contrato', 'nivel_riesgo_id')
        ) {
            DB::statement(
                "UPDATE contrato c
                 LEFT JOIN niveles_riesgo n
                    ON UPPER(TRIM(c.nivel_riesgo)) = UPPER(TRIM(n.nombre))
                 SET c.nivel_riesgo_id = n.id
                 WHERE c.nivel_riesgo IS NOT NULL
                   AND c.nivel_riesgo_id IS NULL"
            );
        }

        Schema::table('contrato', function (Blueprint $table) {
            $table->foreign('nivel_riesgo_id', 'contrato_nivel_riesgo_id_fk')
                ->references('id')
                ->on('niveles_riesgo')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('contrato', 'nivel_riesgo_id')) {
            Schema::table('contrato', function (Blueprint $table) {
                $table->dropForeign('contrato_nivel_riesgo_id_fk');
                $table->dropIndex('contrato_nivel_riesgo_id_idx');
                $table->dropColumn('nivel_riesgo_id');
            });
        }
    }
};
