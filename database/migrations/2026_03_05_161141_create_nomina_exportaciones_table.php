<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nomina_exportaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('id_periodo');
            $table->unsignedBigInteger('id_empresa');
            $table->string('formato', 50); // CSV, ACH, etc.
            $table->dateTime('fecha_generacion');
            $table->string('doc_usuario', 12); // Quien generó (Ref a usuario.doc: string(12))
            $table->integer('total_empleados');
            $table->decimal('total_pagado', 15, 2);
            $table->string('archivo_path', 255)->nullable();

            $table->foreign('id_periodo')->references('id_periodo')->on('periodo_liquidacion')->cascadeOnDelete();
            $table->foreign('id_empresa')->references('id_empresa')->on('empresa')->cascadeOnDelete();
            $table->foreign('doc_usuario')->references('doc')->on('usuario');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_exportaciones');
    }
};
