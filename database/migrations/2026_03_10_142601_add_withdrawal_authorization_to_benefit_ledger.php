<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expand the benefit_ledger.movement_type enum to include 'withdrawal' and 'authorization'.
 *
 * MySQL does not support ALTER COLUMN ... ADD ENUM VALUE, so we must use a raw ALTER statement.
 */
return new class extends Migration {
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `benefit_ledger` MODIFY COLUMN `movement_type` ENUM(
            'accrual',
            'payment',
            'adjustment',
            'initial',
            'withdrawal',
            'authorization'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Revert to original enum values.
        // WARNING: This will fail if any rows have 'withdrawal' or 'authorization'.
        DB::statement("ALTER TABLE `benefit_ledger` MODIFY COLUMN `movement_type` ENUM(
            'accrual',
            'payment',
            'adjustment',
            'initial'
        ) NOT NULL");
    }
};
