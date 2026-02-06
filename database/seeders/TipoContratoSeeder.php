<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoContratoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia sin truncate (por si hay llaves foráneas)
        DB::table('tipo_contrato')->delete();

        DB::table('tipo_contrato')->insert([
            [
                'id_tipo_contrato' => 1,
                'nombre' => 'Término indefinido',
                'seguridad_social' => 1,
            ],
            [
                'id_tipo_contrato' => 2,
                'nombre' => 'Término fijo',
                'seguridad_social' => 1,
            ],
            [
                'id_tipo_contrato' => 3,
                'nombre' => 'Obra o labor',
                'seguridad_social' => 1,
            ],
            [
                'id_tipo_contrato' => 4,
                'nombre' => 'Aprendizaje',
                'seguridad_social' => 0,
            ],
            [
                'id_tipo_contrato' => 5,
                'nombre' => 'Prácticas',
                'seguridad_social' => 0,
            ],
            [
                'id_tipo_contrato' => 6,
                'nombre' => 'Prestación de servicios',
                'seguridad_social' => 0,
            ],
        ]);
    }
}
