<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {Schema::create('payroll_parameters', function (Blueprint $table) {
    $table->id();

    $table->decimal('smmlv', 12, 2);
    $table->decimal('auxilio_transporte', 12, 2);
    $table->integer('auxilio_transporte_tope');

    $table->decimal('eps_employee', 5, 4);
    $table->decimal('pension_employee', 5, 4);
    $table->decimal('fondo_solidaridad', 5, 4);

    $table->decimal('eps_employer', 5, 4);
    $table->decimal('pension_employer', 5, 4);
    $table->decimal('arl_riesgo_1', 5, 4);
    $table->decimal('caja_compensacion', 5, 4);
  

    $table->integer('fondo_solidaridad_threshold');
    $table->integer('horas_mes');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_parameters');
    }
};
