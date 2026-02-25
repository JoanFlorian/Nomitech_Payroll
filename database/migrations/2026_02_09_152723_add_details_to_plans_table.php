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
        Schema::table('plan', function (Blueprint $table) {
            $table->boolean('destacado')->default(false)->after('stripe_price_id');
            $table->integer('orden')->default(0)->after('destacado');
            $table->json('features')->nullable()->after('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite' && config('database.connections.' . config('database.default') . '.driver') !== 'sqlite') {
            Schema::table('plan', function (Blueprint $table) {
                if (Schema::hasColumn('plan', 'destacado')) {
                    $table->dropColumn('destacado');
                }
                if (Schema::hasColumn('plan', 'orden')) {
                    $table->dropColumn('orden');
                }
                if (Schema::hasColumn('plan', 'features')) {
                    $table->dropColumn('features');
                }
            });
        }
    }
};
