<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('metodo_pago')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $metodosPago = [
            'transferencia_bancaria',
            'billetera_digital',
            'efectivo',
        ];
        
        foreach ($metodosPago as $metodo) {
            DB::table('metodo_pago')->insert([
                'nombre' => $metodo,
            ]);
        }
    }
}
