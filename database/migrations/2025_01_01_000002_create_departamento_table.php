<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamento', function (Blueprint $table) {
            $table->integer('id_departamento')->autoIncrement();
            $table->string('nombre', 60); // VARCHAR(60) NOT NULL
            $table->integer('id_pais'); // INT NOT NULL

            $table->foreign('id_pais')->references('id_pais')->on('pais')->onDelete('restrict');

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento');
    }
};
