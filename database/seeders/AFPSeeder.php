<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Afp;

class AFPSeeder extends Seeder
{
    public function run(): void
    {
        $afpList = [

            [
                'id_afp' => 1,
                'nombre' => 'PROTECCION',
                'empresa_nit' => null,
                'codigo_pila' => 'AFP001',
                'telefono' => '018000525800',
                'direccion' => 'Medellín, Antioquia',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_afp' => 2,
                'nombre' => 'COLFONDOS',
                'empresa_nit' => null,
                'codigo_pila' => 'AFP002',
                'telefono' => '018000510000',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_afp' => 3,
                'nombre' => 'PORVENIR',
                'empresa_nit' => null,
                'codigo_pila' => 'AFP005',
                'telefono' => '018000510800',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_afp' => 4,
                'nombre' => 'SKANDIA',
                'empresa_nit' => null,
                'codigo_pila' => 'AFP004',
                'telefono' => '018000517526',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

        ];

        foreach ($afpList as $afp) {
            DB::table('afp')->updateOrInsert(
                ['id_afp' => $afp['id_afp']],
                $afp
            );
        }

        $this->command->info('✅ AFP cargadas: ' . count($afpList) . ' registros');
    }
}