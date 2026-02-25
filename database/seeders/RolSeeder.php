<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total a todos los módulos y permisos del sistema'],
            ['nombre' => 'Auxiliar RRHH', 'descripcion' => 'Acceso a módulos y permisos asignados por el administrador'],
            ['nombre' => 'Empleado', 'descripcion' => 'Acceso únicamente al módulo de desprendibles'],
            ['nombre' => 'Super admin', 'descripcion' => 'Acceso únicamente a los modulos administrativos del sistema'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['nombre' => $rol['nombre']], ['descripcion' => $rol['descripcion']]);
        }
    }
}
