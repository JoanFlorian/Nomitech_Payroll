<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Fixes SQL truncation error by adding 'pending_payroll' to the status enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `benefit_ledger` MODIFY COLUMN `status` ENUM(
            'pending',
            'processed',
            'reported',
            'pending_payroll'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `benefit_ledger` MODIFY COLUMN `status` ENUM(
            'pending',
            'processed',
            'reported'
        ) NULL");
    }
};
