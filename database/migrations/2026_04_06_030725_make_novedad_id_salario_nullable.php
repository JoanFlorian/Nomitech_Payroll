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
        Schema::table('novedad', function (Blueprint $table) {
            // Drop FK first, then change column to nullable, re-add FK with nullOnDelete
            $table->dropForeign(['id_salario']);
            $table->unsignedBigInteger('id_salario')->nullable()->change();
            $table->foreign('id_salario')->references('id_salario')->on('salario')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            $table->dropForeign(['id_salario']);
            $table->unsignedBigInteger('id_salario')->nullable(false)->change();
            $table->foreign('id_salario')->references('id_salario')->on('salario');
        });
    }
};
