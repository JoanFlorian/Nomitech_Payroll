<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pila_detalle_empleado') && Schema::hasColumn('pila_detalle_empleado', 'aporte_arl')) {
            DB::statement('ALTER TABLE pila_detalle_empleado MODIFY aporte_arl DECIMAL(14,6) NOT NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pila_detalle_empleado') && Schema::hasColumn('pila_detalle_empleado', 'aporte_arl')) {
            DB::statement('ALTER TABLE pila_detalle_empleado MODIFY aporte_arl DECIMAL(14,2) NOT NULL DEFAULT 0');
        }
    }
};
