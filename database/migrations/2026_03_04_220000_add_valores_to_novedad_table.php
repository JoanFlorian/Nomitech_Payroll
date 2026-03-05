<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            $table->decimal('pago_manual', 12, 2)->nullable()->after('cantidad');
            $table->decimal('valor_novedad', 12, 2)->default(0)->after('pago_manual');
        });
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            $table->dropColumn(['pago_manual', 'valor_novedad']);
        });
    }
};
