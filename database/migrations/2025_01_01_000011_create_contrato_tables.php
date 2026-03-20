<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void 
    {
            Schema::create('contrato', function (Blueprint $table) {
        $table->unsignedBigInteger('id_contrato')->autoIncrement();

        // FKs
        $table->unsignedBigInteger('id_empresa');
        $table->string('doc', 20);

        $table->unsignedBigInteger('id_tipo_contrato')->nullable();
        $table->unsignedBigInteger('id_tipo_trabajador'); 
        $table->unsignedBigInteger('id_sub_tipo_trabajador')->nullable();
        $table->unsignedBigInteger('id_forma_pago')->nullable();
        $table->unsignedBigInteger('id_metodo_pago')->nullable();

        $table->unsignedBigInteger('id_arl')->nullable();
        $table->unsignedBigInteger('id_eps')->nullable();
        $table->unsignedBigInteger('id_afp')->nullable();

        $table->boolean('alto_riesgo')->default(false);
        $table->string('nivel_riesgo')->nullable(); // Legacy col used for migration to ID

        $table->decimal('salario', 12, 2)->nullable(); // Used for current wage tracking
        $table->integer('horas_diarias')->nullable(); 
        $table->string('codigo_interno', 20)->nullable();

        $table->date('fecha_inicio');
        $table->date('fecha_fin')->nullable();

        $table->decimal('salario_base', 12, 2);
        $table->boolean('activo')->default(true);

        
        $table->foreign('id_empresa')->references('id_empresa')->on('empresa')->cascadeOnDelete();
        $table->foreign('doc')->references('doc')->on('usuario')->cascadeOnDelete();

        $table->foreign('id_tipo_contrato')->references('id_tipo_contrato')->on('tipo_contrato');
        $table->foreign('id_tipo_trabajador')->references('id_tipo_trabajador')->on('tipo_trabajador');
        $table->foreign('id_sub_tipo_trabajador')->references('id_sub_tipo_trabajador')->on('sub_tipo_trabajador');
        $table->foreign('id_forma_pago')->references('id_forma_pago')->on('forma_pago');
        $table->foreign('id_metodo_pago')->references('id_metodo_pago')->on('metodo_pago');

        $table->foreign('id_eps')->references('id_eps')->on('eps');
        $table->foreign('id_afp')->references('id_afp')->on('afp');
        $table->foreign('id_arl')->references('id_arl')->on('arl');

        $table->timestamps();
    });
        

        Schema::create('cuenta', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cuenta')->autoIncrement();
            $table->unsignedBigInteger('id_contrato');
            $table->unsignedBigInteger('id_tipo_cuenta');
            $table->unsignedBigInteger('id_banco');
            $table->string('numero_cuenta', 34);
            $table->boolean('activo')->default(true);

            $table->tinyInteger('active_flag')->virtualAs('CASE WHEN activo THEN 1 ELSE NULL END');

            $table->foreign('id_contrato')->references('id_contrato')->on('contrato')->cascadeOnDelete();
            $table->foreign('id_tipo_cuenta')->references('id_tipo_cuenta')->on('tipo_cuenta');
            $table->foreign('id_banco')->references('id_banco')->on('banco');

            $table->unique(['id_contrato', 'active_flag'], 'uq_contrato_active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta');
        Schema::dropIfExists('contrato');
    }
};