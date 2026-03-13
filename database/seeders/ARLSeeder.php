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
                'id_arl' => 800088702,
                'nombre' => 'ARL SURA',
                'codigo_pila' => 'ARL001',
                'telefono' => '018000511414',
                'direccion' => 'Medellín, Antioquia',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_arl' => 860011153,
                'nombre' => 'POSITIVA ARL',
                'codigo_pila' => 'ARL002',
                'telefono' => '018000111170',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_arl' => 860002503,
                'nombre' => 'COLMENA ARL',
                'codigo_pila' => 'ARL003',
                'telefono' => '018000113390',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_arl' => 860002183,
                'nombre' => 'AXA COLPATRIA ARL',
                'codigo_pila' => 'ARL004',
                'telefono' => '018000512620',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_arl' => 860002180,
                'nombre' => 'SEGUROS BOLIVAR ARL',
                'codigo_pila' => 'ARL005',
                'telefono' => '018000123322',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_arl' => 860006015,
                'nombre' => 'LA EQUIDAD ARL',
                'codigo_pila' => 'ARL006',
                'telefono' => '018000113535',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_arl' => 860037707,
                'nombre' => 'ALFA ARL',
                'codigo_pila' => 'ARL007',
                'telefono' => '018000111600',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
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