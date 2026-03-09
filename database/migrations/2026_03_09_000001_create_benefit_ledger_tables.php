<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('benefit_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();

            // Multitenant isolation
            $table->unsignedBigInteger('tenant_id');

            // Employee reference (prestaciones pertenecen al empleado, no al contrato)
            $table->string('employee_id', 20);

            // Contract context (optional, for calculation traceability)
            $table->unsignedBigInteger('contract_id')->nullable();

            // Benefit classification
            $table->enum('benefit_type', [
                'prima',
                'cesantias',
                'intereses_cesantias',
                'vacaciones'
            ]);

            // Movement classification
            $table->enum('movement_type', [
                'accrual',    // causación
                'payment',    // pago/liquidación
                'adjustment', // ajuste manual
                'initial'     // saldo inicial / migración
            ]);

            $table->decimal('amount', 14, 2);

            // Period association (nullable for initial/adjustment movements)
            $table->integer('period_id')->nullable();

            // Source of the movement
            $table->string('source', 50)->default('payroll');

            // Free-text reference for auditing
            $table->string('reference', 255)->nullable();

            // Foreign keys
            $table->foreign('tenant_id')->references('id_empresa')->on('empresa')->cascadeOnDelete();
            $table->foreign('employee_id')->references('doc')->on('usuario')->cascadeOnDelete();
            $table->foreign('contract_id')->references('id_contrato')->on('contrato')->nullOnDelete();
            $table->foreign('period_id')->references('id_periodo')->on('periodo_liquidacion')->nullOnDelete();

            // Indexes for common queries
            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'benefit_type']);
            $table->index(['period_id']);

            $table->timestamps();
        });

        Schema::create('benefit_balance', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();

            $table->unsignedBigInteger('tenant_id');
            $table->string('employee_id', 20);

            $table->decimal('prima_balance', 14, 2)->default(0);
            $table->decimal('cesantias_balance', 14, 2)->default(0);
            $table->decimal('intereses_balance', 14, 2)->default(0);
            $table->decimal('vacaciones_balance', 14, 2)->default(0);

            // Foreign keys
            $table->foreign('tenant_id')->references('id_empresa')->on('empresa')->cascadeOnDelete();
            $table->foreign('employee_id')->references('doc')->on('usuario')->cascadeOnDelete();

            // One balance row per employee per tenant
            $table->unique(['tenant_id', 'employee_id']);

            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_balance');
        Schema::dropIfExists('benefit_ledger');
    }
};
