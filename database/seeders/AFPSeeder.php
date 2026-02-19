<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AFPSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('afp')->delete();

        DB::table('afp')->insert([
            ['nombre' => 'Protección'],
            ['nombre' => 'Colfondos'],
            ['nombre' => 'Porvenir'],
            ['nombre' => 'Old Mutua'],
        ]);
    }
}
