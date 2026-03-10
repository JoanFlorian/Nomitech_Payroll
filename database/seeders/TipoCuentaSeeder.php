<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCuentaSeeder extends Seeder
{
    public function run(): void
    {
        // Tipos de cuenta según Resolución DIAN 000013 de 2021
        $tiposCuenta = [
            ['nombre' => 'Cuenta de Ahorros'],
            ['nombre' => 'Cuenta Corriente'],
            ['nombre' => 'Cuenta de Inversión a Término'],
            ['nombre' => 'Cuenta de Nómina'],
            ['nombre' => 'Cuenta de Pagos Especiales'],
        ];

        foreach ($tiposCuenta as $tipo) {
            DB::table('tipo_cuenta')->updateOrInsert(
                ['nombre' => $tipo['nombre']],
                $tipo
            );
        }
    }
}
