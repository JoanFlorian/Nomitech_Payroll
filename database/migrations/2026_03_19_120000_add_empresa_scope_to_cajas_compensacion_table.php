<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas_compensacion', function (Blueprint $table) {
            if (!Schema::hasColumn('cajas_compensacion', 'empresa_nit')) {
                $table->string('empresa_nit', 20)->nullable()->after('nombre');
            }

            if (!Schema::hasColumn('cajas_compensacion', 'origen')) {
                $table->string('origen', 20)->default('system')->after('empresa_nit');
            }

            if (!Schema::hasColumn('cajas_compensacion', 'estado')) {
                $table->boolean('estado')->default(true)->after('origen');
            }
        });

        DB::table('cajas_compensacion')
            ->whereNull('origen')
            ->update([
                'origen' => 'system',
                'estado' => true,
            ]);

        Schema::table('cajas_compensacion', function (Blueprint $table) {
            $indexes = [
                'cajas_compensacion_empresa_nit_index' => 'empresa_nit',
                'cajas_compensacion_origen_index' => 'origen',
                'cajas_compensacion_estado_index' => 'estado',
                'cajas_compensacion_nombre_index' => 'nombre',
            ];

            foreach ($indexes as $indexName => $column) {
                try {
                    $table->index($column, $indexName);
                } catch (\Throwable $exception) {
                    // Keep migration idempotent when the index already exists.
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('cajas_compensacion', function (Blueprint $table) {
            foreach ([
                'cajas_compensacion_nombre_index',
                'cajas_compensacion_empresa_nit_index',
                'cajas_compensacion_origen_index',
                'cajas_compensacion_estado_index',
            ] as $indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable $exception) {
                    // Ignore rollback when the index does not exist.
                }
            }

            if (Schema::hasColumn('cajas_compensacion', 'estado')) {
                $table->dropColumn('estado');
            }

            if (Schema::hasColumn('cajas_compensacion', 'origen')) {
                $table->dropColumn('origen');
            }

            if (Schema::hasColumn('cajas_compensacion', 'empresa_nit')) {
                $table->dropColumn('empresa_nit');
            }
        });
    }
};