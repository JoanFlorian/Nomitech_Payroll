<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Roles;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Roles::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso total a todos los módulos y permisos del sistema'
        ]);

        Roles::create([
            'nombre' => 'Auxiliar RRHH',
            'descripcion' => 'Acceso a módulos y permisos asignados por el administrador'
        ]);

        Roles::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Acceso únicamente al módulo de desprendibles'
        ]);
    }
}
