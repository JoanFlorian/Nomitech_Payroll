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
        Schema::table('plan', function (Blueprint $table) {
            if (!Schema::hasColumn('plan', 'max_admins')) {
                $table->integer('max_admins')->default(1)->after('num_empl');
            }
            if (!Schema::hasColumn('plan', 'max_auxiliares')) {
                $table->integer('max_auxiliares')->default(0)->after('max_admins');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan', function (Blueprint $table) {
            $table->dropColumn(['max_admins', 'max_auxiliares']);
        });
    }
};
