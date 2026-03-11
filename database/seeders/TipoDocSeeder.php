<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Cedula de ciudadanía'],
            ['nombre' => 'Cédula de extranjería'],
            ['nombre' => 'NIT'],
            ['nombre' => 'Tarjeta de identidad'],
            ['nombre' => 'Pasaporte'],
            ['nombre' => 'Registro civil'],
            ['nombre' => 'NIT de otro país'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_doc')->insertOrIgnore($tipo);
        }

        echo "✅ Tipos de documento cargados\n";
    }
}
