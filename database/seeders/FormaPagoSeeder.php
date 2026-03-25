<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormaPagoSeeder extends Seeder
{
    public function run(): void
    {
        $formasPago = [
            ['id_forma_pago' => 1, 'nombre' => 'Contado'],
        ];

        foreach ($formasPago as $forma) {
            DB::table('forma_pago')->updateOrInsert(
                ['nombre' => $forma['nombre']],
                ['nombre' => $forma['nombre']]
            );
        }
    }
}
