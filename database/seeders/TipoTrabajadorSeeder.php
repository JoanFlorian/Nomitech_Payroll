<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoTrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_trabajador')->delete();

        DB::table('tipo_trabajador')->insert([
            ['id_tipo_trabajador' => '1', 'nombre' => 'Dependiente'],
        ]);
    }
}
