<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EPSSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('eps')->delete();

        DB::table('eps')->insert([
            ['nombre' => 'Nueva EPS'],
            ['nombre' => 'Coosalud'],
            ['nombre' => 'Mutual Ser'],
            ['nombre' => 'Salud Mía'],
            ['nombre' => 'EPS Sura'],
            ['nombre' => 'Sanitas EPS'],
            ['nombre' => 'Salud Total'],
            ['nombre' => 'Famisanar'],
            ['nombre' => 'Compensar'],
            ['nombre' => 'Aliansalud'],
            ['nombre' => 'SOS'],
            ['nombre' => 'Comfenalco Valle'],
            ['nombre' => 'EPM EPS'],
            ['nombre' => 'Fondo Pasivo Social FFCC'],
            ['nombre' => 'Emssanar'],
            ['nombre' => 'Capital Salud'],
            ['nombre' => 'Savia Salud'],
            ['nombre' => 'Asmet Salud'],
            ['nombre' => 'Cajacopi'],
            ['nombre' => 'Capresoca'],
            ['nombre' => 'EPS Familiar'],
            ['nombre' => 'Comfachocó'],
            ['nombre' => 'Comfaoriente'],
            ['nombre' => 'AIC EPSI'],
            ['nombre' => 'Anas Wayuu EPSI'],
            ['nombre' => 'Dusakawi EPSI'],
            ['nombre' => 'Mallamas EPSI'],
            ['nombre' => 'Pijaos Salud EPSI'],
        ]);
    }
}
