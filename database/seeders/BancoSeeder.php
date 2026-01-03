<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banco;

class BancoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bancos = [
            ['nombre' => 'Banco de Bogotá', 'codigo' => '001'],
            ['nombre' => 'Banco Popular', 'codigo' => '002'],
            ['nombre' => 'Corpbanca Itaú', 'codigo' => '006'],
            ['nombre' => 'Bancolombia', 'codigo' => '007'],
            ['nombre' => 'Citibank', 'codigo' => '009'],
            ['nombre' => 'Banco Sudameris', 'codigo' => '012'],
            ['nombre' => 'BBVA', 'codigo' => '013'],
            ['nombre' => 'Itaú', 'codigo' => '014'],
            ['nombre' => 'Scotiabank Colpatria', 'codigo' => '019'],
            ['nombre' => 'Banco de Occidente', 'codigo' => '023'],
            ['nombre' => 'Banco Caja Social BCSC', 'codigo' => '032'],
            ['nombre' => 'Bancoldex S.A.', 'codigo' => '031'],
            ['nombre' => 'Banco Agrario', 'codigo' => '040'],
            ['nombre' => 'Banco Mundo Mujer', 'codigo' => '047'],
            ['nombre' => 'Banco Davivienda', 'codigo' => '051'],
            ['nombre' => 'Banco Av. Villas', 'codigo' => '052'],
            ['nombre' => 'Banco W S.A.', 'codigo' => '053'],
            ['nombre' => 'Bancamía S.A.', 'codigo' => '059'],
            ['nombre' => 'Banco Pichincha', 'codigo' => '060'],
            ['nombre' => 'Bancoomeva', 'codigo' => '061'],
            ['nombre' => 'Banco Falabella S.A.', 'codigo' => '062'],
            ['nombre' => 'Banco Finandina S.A.', 'codigo' => '063'],
            ['nombre' => 'Banco Santander de Negocios Colombia', 'codigo' => '065'],
            ['nombre' => 'Coopcentral S.A.', 'codigo' => '066'],
            ['nombre' => 'MiBanco S.A.', 'codigo' => '067'],
            ['nombre' => 'Banco Serfinanza S.A.', 'codigo' => '069'],
            ['nombre' => 'Lulo Bank S.A.', 'codigo' => '070'],
            ['nombre' => 'Banco J.P. Morgan Colombia S.A.', 'codigo' => '071'],
        ];

        foreach ($bancos as $banco) {
            Banco::firstOrCreate(
                ['codigo' => $banco['codigo']],
                ['nombre' => $banco['nombre']]
            );
        }
    }
}
