<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodo_liquidacion', function (Blueprint $table) {
            $table->integer('id_periodo')->autoIncrement();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            // CHECK constraint for dates
            
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('salario', function (Blueprint $table) {
            $table->integer('id_salario')->autoIncrement();
            $table->integer('id_contrato');
            $table->integer('id_periodo');
            $table->integer('id_estado')->default(1);

            $table->decimal('auxilio_transporte', 12, 2)->default(0);
            $table->decimal('horas_extra', 12, 2)->default(0);
            $table->decimal('bonificaciones', 12, 2)->default(0);
            $table->decimal('comisiones', 12, 2)->default(0);
            $table->decimal('otros_devengos', 12, 2)->default(0);
            $table->decimal('arl', 12, 2)->default(0);
            $table->decimal('eps', 12, 2)->default(0);
            $table->decimal('afp', 12, 2)->default(0);
            $table->decimal('seguridad_social', 12, 2)->default(0);
            $table->decimal('aporte_fp', 12, 2)->default(0);
            $table->decimal('retencion_fuente', 12, 2)->default(0);
            $table->decimal('embargo_fiscal', 12, 2)->default(0);
            $table->decimal('pension_voluntaria', 12, 2)->default(0);

            $table->integer('dias_a_trabajar')->default(0);
            $table->integer('horas_mensual')->default(0);
            $table->date('fecha_pago')->nullable();

            $table->foreign('id_contrato')->references('id_contrato')->on('contrato');
            $table->foreign('id_periodo')->references('id_periodo')->on('periodo_liquidacion');
            $table->foreign('id_estado')->references('id_estado')->on('estado');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('provision', function (Blueprint $table) {
            $table->integer('id_provision')->autoIncrement();
            $table->integer('id_periodo');
            $table->integer('id_contrato');

            $table->decimal('cesantias', 12, 2)->default(0);
            $table->decimal('intereses_cesantias', 12, 2)->default(0);
            $table->decimal('prima', 12, 2)->default(0);

            $table->foreign('id_periodo')->references('id_periodo')->on('periodo_liquidacion');
            $table->foreign('id_contrato')->references('id_contrato')->on('contrato');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('novedad', function (Blueprint $table) {
            $table->integer('id_novedad')->autoIncrement();
            $table->integer('id_tipo_novedad');
            $table->integer('id_salario');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('pago', 12, 2)->default(0);

            $table->foreign('id_tipo_novedad')->references('id_tipo_novedad')->on('tipo_novedad');
            $table->foreign('id_salario')->references('id_salario')->on('salario');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('hora_recargo_extra', function (Blueprint $table) {
            $table->integer('id_hora')->autoIncrement();
            $table->integer('id_tipo_hora_recargo');
            $table->integer('id_salario');

            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('pago', 12, 2)->default(0);

            $table->foreign('id_tipo_hora_recargo')->references('id_tipo_hora_recargo')->on('tipo_hora_recargo');
            $table->foreign('id_salario')->references('id_salario')->on('salario');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hora_recargo_extra');
        Schema::dropIfExists('novedad');
        Schema::dropIfExists('provision');
        Schema::dropIfExists('salario');
        Schema::dropIfExists('periodo_liquidacion');
    }
};
