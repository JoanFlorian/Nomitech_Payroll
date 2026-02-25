<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DuskDuplicateTestSeeder extends Seeder
{
    /**
     * Data de ejemplo para forzar un constraint unique o validación unique.
     */
    public function run(): void
    {
        // Simulando que ya existe el Código BOG-999 en ciudades para 
        // poder testear con Dusk que el formulario arroje error
        // al intentar crear BOG-999 nuevamente
        DB::table('departamento')->insertOrIgnore([
            'id_departamento' => 1,
            'codigo' => 'DEP-01',
            'nombre' => 'Departamento de Prueba'
        ]);

        DB::table('ciudad')->insertOrIgnore([
            'codigo' => 'BOG-999',
            'nombre' => 'Bogota Duplicada',
            'id_departamento' => 1,
        ]);
    }
}
