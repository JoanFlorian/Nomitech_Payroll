<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SubTipoTrabajador;

class SubTipoTrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        $datos = [
            ['id_sub_tipo_trabajador' => 0, 'nombre' => 'ninguno'],
            ['id_sub_tipo_trabajador' => 1, 'nombre' => 'Dependiente pensionado por vejez activo'],
        ];

        foreach ($datos as $dato) {
            SubTipoTrabajador::updateOrCreate(
                ['id_sub_tipo_trabajador' => $dato['id_sub_tipo_trabajador']],
                ['nombre' => $dato['nombre']]
            );
        }
    }
}
