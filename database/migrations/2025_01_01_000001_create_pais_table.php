<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pais', function (Blueprint $table) {
            $table->integer('id_pais')->autoIncrement();
            $table->string('nombre', 60)->unique(); // VARCHAR(60) NOT NULL UNIQUE
            $table->string('nombre_oficial', 100)->nullable();
            $table->char('codigo_alfa2', 2)->nullable();
            $table->char('codigo_alfa3', 3)->nullable();
            $table->smallInteger('codigo_numerico')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pais');
    }
};
