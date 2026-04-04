<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            if (!Schema::hasColumn('novedad', 'soporte_medico_path')) {
                $table->string('soporte_medico_path')->nullable()->after('certificado_medico');
            }

            if (!Schema::hasColumn('novedad', 'soporte_medico_original_name')) {
                $table->string('soporte_medico_original_name')->nullable()->after('soporte_medico_path');
            }

            if (!Schema::hasColumn('novedad', 'soporte_medico_mime')) {
                $table->string('soporte_medico_mime', 100)->nullable()->after('soporte_medico_original_name');
            }

            if (!Schema::hasColumn('novedad', 'soporte_medico_size')) {
                $table->unsignedBigInteger('soporte_medico_size')->nullable()->after('soporte_medico_mime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('novedad', function (Blueprint $table) {
            foreach (['soporte_medico_size', 'soporte_medico_mime', 'soporte_medico_original_name', 'soporte_medico_path'] as $column) {
                if (Schema::hasColumn('novedad', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
