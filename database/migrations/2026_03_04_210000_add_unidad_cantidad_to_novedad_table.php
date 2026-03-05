<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            $table->string('unidad_cantidad', 10)->default('dias')->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            $table->dropColumn('unidad_cantidad');
        });
    }
};
