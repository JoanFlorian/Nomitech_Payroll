<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomina_electronica', function (Blueprint $table) {
            $table->integer('id_nomina')->autoIncrement();
            $table->integer('id_salario');

            $table->string('cune', 255)->unique();
            $table->dateTime('fecha_generacion');

            $table->boolean('novedad')->default(false);
            $table->boolean('nota_ajuste')->default(false);

            $table->string('pdf_ruta', 500)->nullable();

            $table->foreign('id_salario')->references('id_salario')->on('salario');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomina_electronica');
    }
};
