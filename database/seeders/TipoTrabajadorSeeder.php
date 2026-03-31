<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoTrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        $tiposTrabajador = [
            ['id_tipo_trabajador' => 1, 'nombre' => 'Dependiente'],
            ['id_tipo_trabajador' => 2, 'nombre' => 'Independiente'],
            ['id_tipo_trabajador' => 12, 'nombre' => 'Aprendices del Sena en etapa lectiva'],
            ['id_tipo_trabajador' => 19, 'nombre' => 'Aprendices del SENA en etapa productiva'],
        ];

        foreach ($tiposTrabajador as $tipo) {
            DB::table('tipo_trabajador')->updateOrInsert(
                ['id_tipo_trabajador' => $tipo['id_tipo_trabajador']],
                $tipo
            );
        }
    }
}
