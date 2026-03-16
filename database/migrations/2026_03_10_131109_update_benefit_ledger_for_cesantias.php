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
        Schema::table('benefit_ledger', function (Blueprint $table) {
            $table->enum('destination', ['employee', 'fund'])->nullable()->after('movement_type');
            $table->enum('status', ['pending', 'processed', 'reported'])->nullable()->after('reference');
            $table->unsignedBigInteger('batch_id')->nullable()->after('status');

            $table->foreign('batch_id')->references('id')->on('severance_batches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('benefit_ledger', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn(['destination', 'status', 'batch_id']);
        });
    }
};
