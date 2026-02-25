<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banco;

class BancoSeeder extends Seeder
{
    public function run(): void
    {
        $bancos = [
            ['id_banco' => '890900313', 'nombre' => 'Banco de Bogotá', 'telefono' => '018000 518877', 'direccion' => 'Carrera 7 # 32-16, Bogotá D.C.'],
            ['id_banco' => '890903290', 'nombre' => 'Banco Popular', 'telefono' => '018000 111113', 'direccion' => 'Carrera 9 # 21-36, Bogotá D.C.'],
            ['id_banco' => '860007840', 'nombre' => 'Bancolombia', 'telefono' => '018000 912345', 'direccion' => 'Carrera 48 # 26-85, Medellín, Antioquia'],
            ['id_banco' => '890900050', 'nombre' => 'Citibank Colombia', 'telefono' => '018000 915000', 'direccion' => 'Calle 70 # 9-76, Bogotá D.C.'],
            ['id_banco' => '890900213', 'nombre' => 'Banco de Occidente', 'telefono' => '018000 911111', 'direccion' => 'Calle 26 # 69-76, Bogotá D.C.'],
            ['id_banco' => '900171118', 'nombre' => 'Banco Davivienda', 'telefono' => '018000 123838', 'direccion' => 'Av. El Dorado # 68C-61, Bogotá D.C.'],
            ['id_banco' => '890900496', 'nombre' => 'Banco AV Villas', 'telefono' => '018000 518500', 'direccion' => 'Carrera 13 # 26A-47, Bogotá D.C.'],
            ['id_banco' => '890901148', 'nombre' => 'Banco Mundo Mujer', 'telefono' => '018000 933111', 'direccion' => 'Calle 5 # 38-61, Cali, Valle del Cauca'],
            ['id_banco' => '830046311', 'nombre' => 'Bancoldex S.A.', 'telefono' => '018000 180505', 'direccion' => 'Calle 28 # 13A-15, Bogotá D.C.'],
            ['id_banco' => '890902294', 'nombre' => 'Banco Agrario de Colombia', 'telefono' => '018000 915000', 'direccion' => 'Carrera 8 # 15-43, Bogotá D.C.'],
            ['id_banco' => '890923411', 'nombre' => 'Banco Falabella S.A.', 'telefono' => '018000 933444', 'direccion' => 'Cra 7 # 75-51, Bogotá D.C.'],
            ['id_banco' => '830048352', 'nombre' => 'Banco Pichincha Colombia', 'telefono' => '018000 411111', 'direccion' => 'Av. 6N # 23N-40, Cali, Valle del Cauca'],
            ['id_banco' => '900374463', 'nombre' => 'Lulo Bank S.A.', 'telefono' => '018000 412345', 'direccion' => 'Calle 93 # 11A-17, Bogotá D.C.'],
            ['id_banco' => '901139311', 'nombre' => 'Banco J.P. Morgan Colombia S.A.', 'telefono' => '018000 933999', 'direccion' => 'Carrera 7 # 71-21, Bogotá D.C.'],
        ];

        foreach ($bancos as $banco) {
            Banco::updateOrCreate(
                ['id_banco' => $banco['id_banco']],
                $banco
            );
        }

        $this->command->info('✅ Bancos cargados: ' . count($bancos) . ' registros');
    }
}