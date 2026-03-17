<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Agregar 'ARL' al ENUM tipo_novedad en historial_contrato
        DB::statement("ALTER TABLE historial_contrato MODIFY COLUMN tipo_novedad ENUM('EPS', 'AFP', 'SALARIO', 'ARL') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Remover 'ARL' del ENUM (revertir a los valores originales)
        DB::statement("ALTER TABLE historial_contrato MODIFY COLUMN tipo_novedad ENUM('EPS', 'AFP', 'SALARIO') NOT NULL");
    }
};
