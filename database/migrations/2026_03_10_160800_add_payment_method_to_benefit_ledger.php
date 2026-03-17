<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds payment_method and payroll_period_id columns to benefit_ledger.
 * Expands movement_type enum with 'scheduled_payment'.
 */
return new class extends Migration {
    public function up(): void
    {
        // 1. Expand movement_type enum to include scheduled_payment
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `benefit_ledger` MODIFY COLUMN `movement_type` ENUM(
                'accrual',
                'payment',
                'adjustment',
                'initial',
                'withdrawal',
                'authorization',
                'scheduled_payment'
            ) NOT NULL");
        }

        // 2. Add payment_method and payroll_period_id columns
        Schema::table('benefit_ledger', function (Blueprint $table) {
            $table->enum('payment_method', ['direct', 'payroll'])->default('direct')->after('status');
            $table->integer('payroll_period_id')->nullable()->after('payment_method');

            $table->foreign('payroll_period_id')
                ->references('id_periodo')
                ->on('periodo_liquidacion')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('benefit_ledger', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_id']);
            $table->dropColumn(['payment_method', 'payroll_period_id']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `benefit_ledger` MODIFY COLUMN `movement_type` ENUM(
                'accrual',
                'payment',
                'adjustment',
                'initial',
                'withdrawal',
                'authorization'
            ) NOT NULL");
        }
    }
};
