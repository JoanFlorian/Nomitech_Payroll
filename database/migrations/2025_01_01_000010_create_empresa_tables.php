<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->integer('id_empresa')->autoIncrement();
            $table->string('nit', 20)->unique();
            $table->string('razon_social', 150);
            $table->string('doc_representante', 20); // siempre un usuario

            $table->integer('id_ciudad');
            $table->string('direccion', 150);
            $table->string('correo', 256)->nullable();
            $table->string('telefono', 20);

            $table->foreign('doc_representante')->references('doc')->on('usuario');
            $table->foreign('id_ciudad')->references('id_ciudad')->on('ciudad');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('usuario_empresa', function (Blueprint $table) {
            $table->string('doc', 20);
            $table->integer('id_empresa');

            $table->primary(['doc', 'id_empresa']);

            $table->foreign('doc')->references('doc')->on('usuario');
            $table->foreign('id_empresa')->references('id_empresa')->on('empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_empresa');
        Schema::dropIfExists('empresa');
    }
};
