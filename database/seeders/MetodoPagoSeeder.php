<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodoPagos = [
            ['nombre' => 'Tarjeta Débito'],
            ['nombre' => 'Transferencia Bancaria'],
            ['nombre' => 'Efectivo'],
            ['nombre' => 'Consignación bancaria'],
            ['nombre' => 'Transferencia Crédito Bancario'],
            ['nombre' => 'Transferencia Crédito'],
            ['nombre' => 'CATS (Nequi, Daviplata, etc.)'],

        ];
        
        foreach ($metodoPagos as $metodo) {
            DB::table('metodo_pago')->updateOrInsert(
                ['nombre' => $metodo['nombre']],
                [
                    'nombre' => $metodo['nombre'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
