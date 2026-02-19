<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCuentaSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos la tabla antes de insertar
        DB::table('tipo_cuenta')->delete();

        // Tipos de cuenta según Resolución DIAN 000013 de 2021
        DB::table('tipo_cuenta')->insert([
            ['nombre' => 'Cuenta de Ahorros'],
            ['nombre' => 'Cuenta Corriente'],
            ['nombre' => 'Cuenta de Inversión a Término'],
            ['nombre' => 'Cuenta de Nómina'],
            ['nombre' => 'Cuenta de Pagos Especiales'],
        ]);
    }
}
