<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubTipoTrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sub_tipo_trabajador')->delete();

        DB::table('sub_tipo_trabajador')->insert([
            ['id_sub_tipo_trabajador' => 0, 'nombre' => 'ninguno'],
            ['id_sub_tipo_trabajador' => 1, 'nombre' => 'Dependiente pensionado por vejez activo'],
            
        ]);
    }
}
