<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodoPagos = [
            ['nombre' => 'Tarjeta Crédito'],
            ['nombre' => 'Tarjeta Débito'],
            ['nombre' => 'Transferencia Bancaria'],
            ['nombre' => 'Efectivo'],
            ['nombre' => 'Cheque'],
            ['nombre' => 'Billetera Digital'],
        ];

        foreach ($metodoPagos as $metodo) {
            DB::table('metodo_pago')->insert([
                'nombre' => $metodo['nombre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
