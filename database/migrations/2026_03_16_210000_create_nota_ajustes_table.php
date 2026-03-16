<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_ajustes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('usuario_id', 20);
            $table->unsignedBigInteger('id_empresa')->nullable();
            $table->text('mensaje');
            $table->string('estado', 20)->default('no_leido');
            $table->timestamps();

            $table->foreign('usuario_id')->references('doc')->on('usuario')->cascadeOnDelete();
            $table->foreign('id_empresa')->references('id_empresa')->on('empresa')->nullOnDelete();
            $table->index(['estado', 'created_at']);
            $table->index('id_empresa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_ajustes');
    }
};