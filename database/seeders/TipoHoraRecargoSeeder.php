<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoHoraRecargoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_hora_recargo')->delete();

        DB::table('tipo_hora_recargo')->insert([
            [
                'nombre' => 'Hora extra diurna',
                'valor' => 25.00,
            ],
            [
                'nombre' => 'Hora extra nocturna',
                'valor' => 75.00,
            ],
            [
                'nombre' => 'Hora extra diurna dominical o festiva',
                'valor' => 100.00,
            ],
            [
                'nombre' => 'Hora extra nocturna dominical o festiva',
                'valor' => 150.00,
            ],
            [
                'nombre' => 'Recargo nocturno',
                'valor' => 35.00,
            ],
            [
                'nombre' => 'Recargo dominical o festivo',
                'valor' => 75.00,
            ],
        ]);
    }
}
