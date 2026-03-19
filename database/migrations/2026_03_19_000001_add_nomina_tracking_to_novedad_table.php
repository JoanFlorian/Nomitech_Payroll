<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            // Indica si esta novedad debe afectar el cálculo de nómina.
            // Útil para registros informativos que no generan devengos ni deducciones.
            if (!Schema::hasColumn('novedad', 'afecta_nomina')) {
                $table->boolean('afecta_nomina')->default(true)->after('certificado_medico');
            }

            // Periodo en el que esta novedad fue efectivamente aplicada en nómina.
            // Para IGE/IRL esto garantiza que solo afecten el periodo de registro.
            if (!Schema::hasColumn('novedad', 'periodo_aplicado_id')) {
                // Debe ser INT firmado para coincidir con periodo_liquidacion.id_periodo
                $table->integer('periodo_aplicado_id')->nullable()->after('afecta_nomina');
            }
        });

        // Aseguramos que sea INT firmado y añadimos la relación si no existe
        if (!$this->foreignKeyExists('novedad', 'novedad_periodo_aplicado_id_foreign')) {
            Schema::table('novedad', function (Blueprint $table) {
                $table->foreign('periodo_aplicado_id')
                    ->references('id_periodo')
                    ->on('periodo_liquidacion')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Verifica si una llave foránea existe en la tabla dada.
     */
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            if ($this->foreignKeyExists('novedad', 'novedad_periodo_aplicado_id_foreign')) {
                $table->dropForeign(['periodo_aplicado_id']);
            }
            if (Schema::hasColumn('novedad', 'afecta_nomina')) {
                $table->dropColumn('afecta_nomina');
            }
            if (Schema::hasColumn('novedad', 'periodo_aplicado_id')) {
                $table->dropColumn('periodo_aplicado_id');
            }
        });
    }
};
