<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado', function (Blueprint $table) {
            $table->integer('id_estado')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('tipo_novedad', function (Blueprint $table) {
            $table->integer('id_tipo_novedad')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('tipo_hora_recargo', function (Blueprint $table) {
            $table->integer('id_tipo_hora_recargo')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->decimal('valor', 12, 2)->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_hora_recargo');
        Schema::dropIfExists('tipo_novedad');
        Schema::dropIfExists('estado');
    }
};
