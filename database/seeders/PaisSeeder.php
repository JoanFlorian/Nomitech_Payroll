<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pais;

class PaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pais::create([
            'nombre' => 'Colombia',
            'nombre_oficial' => 'Colombia',
            'codigo_alfa2' => 'CO',
            'codigo_alfa3' => 'COL',
            'codigo_numerico' => 170
        ]);
    }
}
