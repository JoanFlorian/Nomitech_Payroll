<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\NivelRiesgo;

class NivelRiesgoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveles = [
            ['nombre' => 'Nivel I', 'porcentaje' => 0.00522],
            ['nombre' => 'Nivel II', 'porcentaje' => 0.01044],
            ['nombre' => 'Nivel III', 'porcentaje' => 0.02436],
            ['nombre' => 'Nivel IV', 'porcentaje' => 0.04350],
            ['nombre' => 'Nivel V', 'porcentaje' => 0.06960],
        ];

        foreach ($niveles as $nivel) {
            DB::table('niveles_riesgo')->updateOrInsert(
                ['nombre' => $nivel['nombre']],
                ['porcentaje' => $nivel['porcentaje']]
            );
        }
    }
}
