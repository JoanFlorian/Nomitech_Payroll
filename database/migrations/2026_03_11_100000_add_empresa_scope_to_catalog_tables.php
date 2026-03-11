<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'afp',
        'banco',
        'eps',
        'arl',
        'forma_pago',
        'metodo_pago',
        'rol',
        'tipo_contrato',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (!Schema::hasColumn($table, 'empresa_nit')) {
                    $blueprint->string('empresa_nit', 20)->nullable()->after('nombre');
                }

                if (!Schema::hasColumn($table, 'origen')) {
                    $blueprint->string('origen', 20)->default('system')->after('empresa_nit');
                }

                if (!Schema::hasColumn($table, 'estado')) {
                    $blueprint->boolean('estado')->default(true)->after('origen');
                }
            });

            DB::table($table)
                ->whereNull('origen')
                ->update([
                    'origen' => 'system',
                    'estado' => true,
                ]);

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                try {
                    $blueprint->dropUnique($table . '_nombre_unique');
                } catch (\Throwable $exception) {
                    // Keep migration idempotent when the unique index does not exist.
                }

                $blueprint->index('empresa_nit', $table . '_empresa_nit_index');
                $blueprint->index('origen', $table . '_origen_index');
                $blueprint->index('estado', $table . '_estado_index');
                $blueprint->index('nombre', $table . '_nombre_index');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex($table . '_nombre_index');
                $blueprint->dropIndex($table . '_empresa_nit_index');
                $blueprint->dropIndex($table . '_origen_index');
                $blueprint->dropIndex($table . '_estado_index');

                try {
                    $blueprint->unique('nombre', $table . '_nombre_unique');
                } catch (\Throwable $exception) {
                    // Ignore rollback uniqueness recreation issues when data already duplicates.
                }

                if (Schema::hasColumn($table, 'estado')) {
                    $blueprint->dropColumn('estado');
                }

                if (Schema::hasColumn($table, 'origen')) {
                    $blueprint->dropColumn('origen');
                }

                if (Schema::hasColumn($table, 'empresa_nit')) {
                    $blueprint->dropColumn('empresa_nit');
                }
            });
        }
    }
};
