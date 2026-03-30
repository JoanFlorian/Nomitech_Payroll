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
        Schema::table('nomina_exportaciones', function (Blueprint $table) {
            $table->string('archivo_excel_path')->nullable()->after('archivo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nomina_exportaciones', function (Blueprint $table) {
            $table->dropColumn('archivo_excel_path');
        });
    }
};
