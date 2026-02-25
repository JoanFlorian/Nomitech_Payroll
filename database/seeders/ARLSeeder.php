<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arl;

class ARLSeeder extends Seeder
{
    public function run(): void
    {
        $arlList = [
            [
                'id_arl' => '800088702',
                'nombre' => 'ARL SURA',
                'telefono' => '018000 51 1414',
                'direccion' => 'Calle 49 # 63-57, Medellín, Antioquia'
            ],
            [
                'id_arl' => '860011153',
                'nombre' => 'ARL Positiva',
                'telefono' => '018000 111170',
                'direccion' => 'Carrera 10 # 52-10, Bogotá D.C.'
            ],
            [
                'id_arl' => '860002503',
                'nombre' => 'ARL Colmena (Liberty)',
                'telefono' => '018000 113390',
                'direccion' => 'Carrera 7 # 26-20, Bogotá D.C.'
            ],
            [
                'id_arl' => '860002183',
                'nombre' => 'ARL AXA Colpatria',
                'telefono' => '018000 512620',
                'direccion' => 'Carrera 7 # 24-89, Bogotá D.C.'
            ],
            [
                'id_arl' => '860002180',
                'nombre' => 'ARL Bolívar',
                'telefono' => '018000 123322',
                'direccion' => 'Calle 26 # 69-76, Bogotá D.C.'
            ],
            [
                'id_arl' => '860002183',
                'nombre' => 'ARL Allianz',
                'telefono' => '018000 513500',
                'direccion' => 'Carrera 7 # 77-07, Bogotá D.C.'
            ],
            [
                'id_arl' => '860006015',
                'nombre' => 'ARL La Equidad',
                'telefono' => '018000 113535',
                'direccion' => 'Carrera 68A # 24B-10, Bogotá D.C.'
            ],
            [
                'id_arl' => '860037707',
                'nombre' => 'ARL Seguros de Vida Alfa',
                'telefono' => '018000 111600',
                'direccion' => 'Carrera 13 # 93-40, Bogotá D.C.'
            ],
        ];

        foreach ($arlList as $arl) {
            Arl::updateOrCreate(
                ['id_arl' => $arl['id_arl']],
                $arl
            );
        }

        $this->command->info('✅ ARL cargadas: ' . count($arlList) . ' registros');
    }
}