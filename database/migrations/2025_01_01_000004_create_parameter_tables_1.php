<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_doc', function (Blueprint $table) {
            $table->integer('id_tipo_doc')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('tipo_trabajador', function (Blueprint $table) {
            $table->integer('id_tipo_trabajador')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('sub_tipo_trabajador', function (Blueprint $table) {
            $table->integer('id_sub_tipo_trabajador')->autoIncrement();
            $table->string('nombre', 100)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('banco', function (Blueprint $table) {
            $table->integer('id_banco')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco');
        Schema::dropIfExists('sub_tipo_trabajador');
        Schema::dropIfExists('tipo_trabajador');
        Schema::dropIfExists('tipo_doc');
    }
};
