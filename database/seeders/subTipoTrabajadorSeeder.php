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
        ['nombre' => 'ninguno'],
        ['nombre' => 'Dependiente pensionado por vejez activo'],
        ]);
    }
}
