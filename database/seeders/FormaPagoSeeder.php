<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormaPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('forma_pago')->delete();

        DB::table('forma_pago')->insert([
            ['nombre' => 'Contado'],
            ['nombre' => 'Crédito'],
        ]);
    }
}
