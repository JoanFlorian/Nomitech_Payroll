<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planilla_pila', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_empresa');
            $table->string('periodo', 7); 
            $table->date('fecha_generacion');

            $table->decimal('total_salud', 14, 2)->default(0);
            $table->decimal('total_pension', 14, 2)->default(0);
            $table->decimal('total_arl', 14, 2)->default(0);
            $table->decimal('total_caja', 14, 2)->default(0);

            $table->string('estado')->default('generada');
            $table->string('archivo_generado')->nullable();

            $table->timestamps();
            $table->foreign('id_empresa')
            ->references('id_empresa')
            ->on('empresa')
            ->onDelete('cascade');    
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planilla_pila');
    }
};