<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            if (!Schema::hasColumn('novedad', 'tipo_movimiento')) {
                $table->string('tipo_movimiento', 20)->nullable()->after('pago');
            }

            if (!Schema::hasColumn('novedad', 'afecta_ibc')) {
                $table->boolean('afecta_ibc')->default(false)->after('tipo_movimiento');
            }

            if (!Schema::hasColumn('novedad', 'tipo_novedad_codigo')) {
                $table->string('tipo_novedad_codigo', 20)->nullable()->after('tipo_novedad_nombre');
            }

            if (!Schema::hasColumn('novedad', 'tipo_licencia')) {
                $table->string('tipo_licencia', 40)->nullable()->after('es_remunerado');
            }

            if (!Schema::hasColumn('novedad', 'tipo_incapacidad')) {
                $table->string('tipo_incapacidad', 40)->nullable()->after('tipo_licencia');
            }

            if (!Schema::hasColumn('novedad', 'certificado_medico')) {
                $table->boolean('certificado_medico')->default(false)->after('tipo_incapacidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            foreach (['certificado_medico', 'tipo_incapacidad', 'tipo_licencia', 'tipo_novedad_codigo', 'afecta_ibc', 'tipo_movimiento'] as $column) {
                if (Schema::hasColumn('novedad', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
