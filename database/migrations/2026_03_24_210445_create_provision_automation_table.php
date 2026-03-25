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
    {
        Schema::create('provision_automation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa');
            $table->string('benefit_type'); // prima, cesantias, intereses_cesantias
            $table->string('payment_mode'); // direct, payroll
            $table->integer('execution_day');
            $table->integer('execution_month');
            $table->integer('last_execution_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_empresa')->references('id_empresa')->on('empresa')->onDelete('cascade');
            $table->unique(['id_empresa', 'benefit_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provision_automation');
    }
};
