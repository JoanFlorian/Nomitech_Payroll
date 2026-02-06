<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_contrato', function (Blueprint $table) {
            $table->integer('id_tipo_contrato')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->boolean('seguridad_social')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('tipo_cuenta', function (Blueprint $table) {
            $table->integer('id_tipo_cuenta')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('forma_pago', function (Blueprint $table) {
            $table->integer('id_forma_pago')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('metodo_pago', function (Blueprint $table) {
            $table->integer('id_metodo_pago')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodo_pago');
        Schema::dropIfExists('forma_pago');
        Schema::dropIfExists('tipo_cuenta');
        Schema::dropIfExists('tipo_contrato');
    }
};
