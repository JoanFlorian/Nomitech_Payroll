<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plan', function (Blueprint $table) {
            if (!Schema::hasColumn('plan', 'destacado')) {
                $table->boolean('destacado')->default(false)->after('duracion');
            }

            if (!Schema::hasColumn('plan', 'orden')) {
                $table->integer('orden')->default(0)->after('destacado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plan', function (Blueprint $table) {
            if (Schema::hasColumn('plan', 'destacado')) {
                $table->dropColumn('destacado');
            }

            if (Schema::hasColumn('plan', 'orden')) {
                $table->dropColumn('orden');
            }
        });
    }
};
