<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cesantias_withdrawals', function (Blueprint $table) {
            $table->id();

            $table->string('employee_id', 20);
            $table->unsignedBigInteger('company_id');

            $table->decimal('amount', 14, 2);
            $table->enum('reason', ['housing', 'education']);
            $table->enum('payment_origin', ['company', 'fund']);
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->string('certificate_path', 500)->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')->references('doc')->on('usuario')->cascadeOnDelete();
            $table->foreign('company_id')->references('id_empresa')->on('empresa')->cascadeOnDelete();

            // Indexes
            $table->index(['company_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cesantias_withdrawals');
    }
};
