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
            ['id_sub_tipo_trabajador' => 1, 'nombre' => 'Ninguno'],
            ['id_sub_tipo_trabajador' => 2, 'nombre' => 'Dependiente pensionado por vejez activo'],
        ];

        foreach ($datos as $dato) {
            try {
                DB::table('sub_tipo_trabajador')->updateOrInsert(
                    ['id_sub_tipo_trabajador' => $dato['id_sub_tipo_trabajador']],
                    ['nombre' => $dato['nombre']]
                );
            } catch (\Exception $e) {
                // Ignore duplicate or other errors for master data
            }
        }
    }
}
