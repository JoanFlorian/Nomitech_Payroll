<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ARLSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Arl')->delete();

        DB::table('Arl')->insert([
            ['nombre' => 'ARL SURA'],
            ['nombre' => 'ARL Positiva'],
            ['nombre' => 'ARL Colmena'],
            ['nombre' => 'ARL AXA Colpatria'],
            ['nombre' => 'ARL Bolívar'],
            ['nombre' => 'ARL Allianz'],
            ['nombre' => 'ARL La Equidad'],
            ['nombre' => 'ARL Seguros de Vida Alfa'],
        ]);
    }
}
