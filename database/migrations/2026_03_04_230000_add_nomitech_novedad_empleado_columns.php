<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            if (!Schema::hasColumn('novedad', 'empleado_id')) {
                $table->string('empleado_id', 30)->nullable()->after('id_salario');
            }
            if (!Schema::hasColumn('novedad', 'tipo_novedad_nombre')) {
                $table->string('tipo_novedad_nombre', 100)->nullable()->after('empleado_id');
            }
            if (!Schema::hasColumn('novedad', 'dias')) {
                $table->decimal('dias', 12, 2)->default(0)->after('unidad_cantidad');
            }
            if (!Schema::hasColumn('novedad', 'horas')) {
                $table->decimal('horas', 12, 2)->default(0)->after('dias');
            }
            if (!Schema::hasColumn('novedad', 'valor_calculado')) {
                $table->decimal('valor_calculado', 14, 2)->default(0)->after('pago');
            }
            if (!Schema::hasColumn('novedad', 'salario_base')) {
                $table->decimal('salario_base', 14, 2)->default(0)->after('valor_calculado');
            }
            if (!Schema::hasColumn('novedad', 'fecha')) {
                $table->date('fecha')->nullable()->after('fecha_fin');
            }
            if (!Schema::hasColumn('novedad', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('salario_base');
            }
            if (!Schema::hasColumn('novedad', 'es_remunerado')) {
                $table->boolean('es_remunerado')->default(false)->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            foreach (['es_remunerado', 'observaciones', 'fecha', 'salario_base', 'valor_calculado', 'horas', 'dias', 'tipo_novedad_nombre', 'empleado_id'] as $column) {
                if (Schema::hasColumn('novedad', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
