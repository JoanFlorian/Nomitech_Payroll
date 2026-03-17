<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair migration: the original 2025_01_01_000007 had a syntax issue
     * that prevented cajas_compensacion from being created.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cajas_compensacion')) {
            Schema::create('cajas_compensacion', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->unsignedBigInteger('id_caja')->primary();
                $table->string('codigo_pila', 10)->unique();
                $table->string('nombre', 80);
                $table->string('telefono', 20)->nullable();
                $table->string('direccion', 120)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas_compensacion');
    }
};
