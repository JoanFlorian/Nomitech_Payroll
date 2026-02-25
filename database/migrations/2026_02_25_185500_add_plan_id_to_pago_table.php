<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('licencia_id');
            $table->foreign('plan_id')->references('id')->on('plan')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};
