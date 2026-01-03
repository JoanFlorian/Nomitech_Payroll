<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciudad', function (Blueprint $table) {
            $table->integer('id_ciudad')->autoIncrement();
            $table->string('nombre', 60); // VARCHAR(60) NOT NULL
            $table->string('codigo', 10)->nullable();
            $table->integer('id_departamento'); // INT NOT NULL

            $table->foreign('id_departamento')->references('id_departamento')->on('departamento')->onDelete('restrict');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudad');
    }
};
