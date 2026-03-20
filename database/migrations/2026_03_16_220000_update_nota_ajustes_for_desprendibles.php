<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_ajustes', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_ajustes', 'id_salario')) {
                $table->unsignedBigInteger('id_salario')->nullable()->after('usuario_id');
                $table->foreign('id_salario')->references('id_salario')->on('salario')->nullOnDelete();
                $table->index('id_salario');
            }

            if (!Schema::hasColumn('nota_ajustes', 'respuesta_admin')) {
                $table->text('respuesta_admin')->nullable()->after('mensaje');
            }
        });

        DB::table('nota_ajustes')
            ->where('estado', 'no_leido')
            ->update(['estado' => 'pendiente']);

        DB::table('nota_ajustes')
            ->where('estado', 'leido')
            ->update(['estado' => 'resuelto']);

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE nota_ajustes MODIFY estado VARCHAR(20) NOT NULL DEFAULT 'pendiente'");
        }
    }

    public function down(): void
    {
        DB::table('nota_ajustes')
            ->where('estado', 'pendiente')
            ->update(['estado' => 'no_leido']);

        DB::table('nota_ajustes')
            ->where('estado', 'resuelto')
            ->update(['estado' => 'leido']);

        DB::statement("ALTER TABLE nota_ajustes MODIFY estado VARCHAR(20) NOT NULL DEFAULT 'no_leido'");

        Schema::table('nota_ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('nota_ajustes', 'id_salario')) {
                $table->dropForeign(['id_salario']);
                $table->dropColumn('id_salario');
            }

            if (Schema::hasColumn('nota_ajustes', 'respuesta_admin')) {
                $table->dropColumn('respuesta_admin');
            }
        });
    }
};