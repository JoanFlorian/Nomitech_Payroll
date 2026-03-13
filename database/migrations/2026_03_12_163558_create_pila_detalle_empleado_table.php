<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pila_detalle_empleado', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('planilla_id');
            $table->string('doc_empleado', 20);

            $table->decimal('ibc_salud', 14, 2)->default(0);
            $table->decimal('ibc_pension', 14, 2)->default(0);
            $table->decimal('ibc_arl', 14, 2)->default(0);

            $table->decimal('aporte_salud', 14, 2)->default(0);
            $table->decimal('aporte_pension', 14, 2)->default(0);
            $table->decimal('aporte_arl', 14, 2)->default(0);
            $table->decimal('aporte_caja', 14, 2)->default(0);

            $table->integer('dias_cotizados')->default(30);

            $table->timestamps();

            $table->foreign('planilla_id')
                ->references('id')
                ->on('planilla_pila')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pila_detalle_empleado');
    }
};