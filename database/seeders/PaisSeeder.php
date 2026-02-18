<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pais;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        Pais::updateOrCreate(
            ['nombre' => 'Colombia'], // condición para buscar
            [
                'nombre_oficial' => 'Colombia',
                'codigo_alfa2'   => 'CO',
                'codigo_alfa3'   => 'COL',
                'codigo_numerico'=> 170,
            ]
        );
    }
}
