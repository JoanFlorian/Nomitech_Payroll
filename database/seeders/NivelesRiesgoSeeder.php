<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelesRiesgoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('niveles_riesgo')->insert([
            ['nombre' => 'Nivel I', 'porcentaje' => 0.00522],
            ['nombre' => 'Nivel II', 'porcentaje' => 0.01044],
            ['nombre' => 'Nivel III', 'porcentaje' => 0.02436],
            ['nombre' => 'Nivel IV', 'porcentaje' => 0.04350],
            ['nombre' => 'Nivel V', 'porcentaje' => 0.06960],
        ]);
    }
}