<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->integer('id_rol')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('modulo', function (Blueprint $table) {
            $table->integer('id_modulo')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo');
        Schema::dropIfExists('rol');
    }
};
