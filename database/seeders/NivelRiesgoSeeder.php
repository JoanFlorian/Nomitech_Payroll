<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NivelRiesgo;

class NivelRiesgoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveles = [
            ['nombre' => 'Nivel I', 'porcentaje' => 0.5],
            ['nombre' => 'Nivel II', 'porcentaje' => 1.0],
            ['nombre' => 'Nivel III', 'porcentaje' => 2.5],
            ['nombre' => 'Nivel IV', 'porcentaje' => 4.0],
            ['nombre' => 'Nivel V', 'porcentaje' => 5.0],
        ];

        foreach ($niveles as $nivel) {
            NivelRiesgo::firstOrCreate(
                ['nombre' => $nivel['nombre']],
                ['porcentaje' => $nivel['porcentaje']]
            );
        }
    }
}
