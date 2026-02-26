<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar Rol 1 (Administrador -> Representante Legal)
        DB::table('rol')
            ->where('id_rol', 1)
            ->update([
                'nombre' => 'Representante Legal',
                'descripcion' => 'Tiene la capacidad de crear usuarios'
            ]);

        // Actualizar Rol 2 (Auxiliar RRHH -> Administrador)
        DB::table('rol')
            ->where('id_rol', 2)
            ->update([
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso a módulos y permisos asignados por el representante legal'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir Rol 1
        DB::table('rol')
            ->where('id_rol', 1)
            ->update([
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso total a todos los módulos y permisos del sistema'
            ]);

        // Revertir Rol 2
        DB::table('rol')
            ->where('id_rol', 2)
            ->update([
                'nombre' => 'Auxiliar RRHH',
                'descripcion' => 'Acceso a módulos y permisos asignados por el administrador'
            ]);
    }
};
