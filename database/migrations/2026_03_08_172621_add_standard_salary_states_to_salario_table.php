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
        // Asegurarse de que la columna estado existe (ya existe por una migración previa, pero la estandarizamos)
        // Migramos 'borrador' a 'pendiente' para consistencia
        DB::table('salario')->where('estado', 'borrador')->update(['estado' => 'pendiente']);

        Schema::table('salario', function (Blueprint $table) {
            $table->string('estado')->default('pendiente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salario', function (Blueprint $table) {
            $table->string('estado')->default('liquidado')->change();
        });

        DB::table('salario')->where('estado', 'pendiente')->update(['estado' => 'borrador']);
    }
};
