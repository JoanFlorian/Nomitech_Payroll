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
            ['nombre' => 'Representante Legal', 'descripcion' => 'Tiene la capacidad de crear usuarios'],
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso a módulos y permisos asignados por el representante legal'],
            ['nombre' => 'Trabajador', 'descripcion' => 'Acceso únicamente al módulo de desprendibles'],
            ['nombre' => 'Superadmin', 'descripcion' => 'Acceso únicamente a los modulos administrativos del sistema'],
            ['nombre' => 'Auxiliar de Nómina', 'descripcion' => 'Apoyo en el procesamiento y cargue de novedades'],
            ['nombre' => 'Auditor de Nómina', 'descripcion' => 'Revisión y validación de liquidaciones y reportes'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['nombre' => $rol['nombre']], ['descripcion' => $rol['descripcion']]);
        }
    }
}
