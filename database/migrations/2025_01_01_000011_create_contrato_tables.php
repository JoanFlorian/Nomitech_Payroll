<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato', function (Blueprint $table) {
            $table->integer('id_contrato')->autoIncrement();

            $table->integer('id_empresa');
            $table->string('doc', 20);

            $table->integer('id_tipo_contrato')->nullable();
            $table->integer('id_tipo_trabajador'); // Not nullable
            $table->integer('id_sub_tipo_trabajador')->nullable();
            $table->integer('id_forma_pago')->nullable();
            $table->integer('id_metodo_pago')->nullable();

            $table->integer('id_arl')->nullable();
            $table->integer('id_eps')->nullable();
            $table->integer('id_afp')->nullable();

            $table->boolean('alto_riesgo')->default(false);
            $table->string('nivel_riesgo', 50)->nullable();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->decimal('salario_base', 12, 2); // CHECK constraint not supported natively directly but DB enforces it
            $table->boolean('activo')->default(true);

            $table->foreign('id_empresa')->references('id_empresa')->on('empresa');
            $table->foreign('doc')->references('doc')->on('usuario');
            $table->foreign('id_tipo_contrato')->references('id_tipo_contrato')->on('tipo_contrato');
            $table->foreign('id_tipo_trabajador')->references('id_tipo_trabajador')->on('tipo_trabajador');
            $table->foreign('id_sub_tipo_trabajador')->references('id_sub_tipo_trabajador')->on('sub_tipo_trabajador');
            $table->foreign('id_forma_pago')->references('id_forma_pago')->on('forma_pago');
            $table->foreign('id_metodo_pago')->references('id_metodo_pago')->on('metodo_pago');
            $table->foreign('id_eps')->references('id_eps')->on('eps');
            $table->foreign('id_afp')->references('id_afp')->on('afp');
            $table->foreign('id_arl')->references('id_arl')->on('arl');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
        
        // Add Check for salario_base manually if needed, but not strictly required for migration success unless strict mode
        // DB::statement('ALTER TABLE contrato ADD CONSTRAINT chk_salario_base CHECK (salario_base >= 0)');

        Schema::create('cuenta', function (Blueprint $table) {
            $table->integer('id_cuenta')->autoIncrement();
            $table->integer('id_contrato');
            $table->integer('id_tipo_cuenta');
            $table->integer('id_banco');
            $table->string('numero_cuenta', 34);
            $table->boolean('activo')->default(true);

            // Virtual generated column for One Active Account constraint
            // CHECK SYNTAX: active_flag TINYINT(1) GENERATED ALWAYS AS (CASE WHEN activo THEN 1 ELSE NULL END) VIRTUAL
            $table->tinyInteger('active_flag')->virtualAs('CASE WHEN activo THEN 1 ELSE NULL END');

            $table->foreign('id_contrato')->references('id_contrato')->on('contrato')->onDelete('cascade');
            $table->foreign('id_tipo_cuenta')->references('id_tipo_cuenta')->on('tipo_cuenta');
            $table->foreign('id_banco')->references('id_banco')->on('banco');

            $table->unique(['id_contrato', 'active_flag'], 'uq_contrato_active');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta');
        Schema::dropIfExists('contrato');
    }
};
