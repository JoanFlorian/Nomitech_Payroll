<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_doc')->insert([
            ['nombre' => 'Cedula de ciudadanía'],
            ['nombre' => 'Cédula de extranjería'],
            ['nombre' => 'NIT'],
            ['nombre' => 'Tarjeta de identidad'],
            ['nombre' => 'Pasaporte'],
            ['nombre' => 'Registro civil'],
            ['nombre' => 'NIT de otro país'],


        ]);
    }
}
