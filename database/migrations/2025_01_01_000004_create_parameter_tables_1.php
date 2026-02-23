<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_doc', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tipo_doc')->autoIncrement(); // ✅
            $table->string('nombre', 60)->unique();
            $table->timestamps();
        });

        Schema::create('tipo_trabajador', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tipo_trabajador')->autoIncrement(); // ✅ PK + mismo tipo
            $table->string('nombre', 300)->unique();
            $table->timestamps();
        });

        Schema::create('sub_tipo_trabajador', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sub_tipo_trabajador')->autoIncrement(); // ✅
            $table->string('nombre', 300)->unique();
            $table->timestamps();
        });

        Schema::create('banco', function (Blueprint $table) {
            $table->unsignedBigInteger('id_banco')->autoIncrement(); // ✅
            $table->string('telefono', 50)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('nombre', 60)->unique();
            $table->timestamps();
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