<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banco;

class BancoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CARGAR BANCOS EXISTENTES (Para no perder direccion ni telefono ni nit)
        $bancosOriginales = [
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

        foreach ($bancosOriginales as $banco) {
            Banco::updateOrCreate(
                ['id_banco' => $banco['id_banco']],
                [
                    'nombre' => $banco['nombre'],
                    'telefono' => $banco['telefono'],
                    'direccion' => $banco['direccion']
                ]
            );
        }

        // 2. CODIGOS ACH Y NUEVOS BANCOS
        $bancosACH = [
            ['nombre' => 'AVAL SOLUCIONES TECNOLOGICAS', 'codigo_ach' => '1899'],
            ['nombre' => 'BANCAMIA S.A', 'codigo_ach' => '1059'],
            ['nombre' => 'BANCO AGRARIO', 'codigo_ach' => '1040'],
            ['nombre' => 'BANCO AV VILLAS', 'codigo_ach' => '1052'],
            ['nombre' => 'BANCO BTG PACTUAL', 'codigo_ach' => '1805'],
            ['nombre' => 'BANCO CAJA SOCIAL BCSC SA', 'codigo_ach' => '1032'],
            ['nombre' => 'BANCO CONTACTAR S.A.', 'codigo_ach' => '1819'],
            ['nombre' => 'BANCO COOPERATIVO COOPCENTRAL', 'codigo_ach' => '1066'],
            ['nombre' => 'BANCO CREDIFINANCIERA SA.', 'codigo_ach' => '1558'],
            ['nombre' => 'BANCO DAVIVIENDA SA', 'codigo_ach' => '1051'],
            ['nombre' => 'BANCO DE BOGOTA', 'codigo_ach' => '1001'],
            ['nombre' => 'BANCO DE OCCIDENTE', 'codigo_ach' => '1023'],
            ['nombre' => 'BANCO FALABELLA S.A.', 'codigo_ach' => '1062'],
            ['nombre' => 'BANCO FINANDINA S.A.', 'codigo_ach' => '1063'],
            ['nombre' => 'BANCO GNB SUDAMERIS', 'codigo_ach' => '1012'],
            ['nombre' => 'BANCO J.P. MORGAN COLOMBIA S.A', 'codigo_ach' => '1071'],
            ['nombre' => 'BANCO MUNDO MUJER', 'codigo_ach' => '1047'],
            ['nombre' => 'BANCO PICHINCHA', 'codigo_ach' => '1060'],
            ['nombre' => 'BANCO POPULAR', 'codigo_ach' => '1002'],
            ['nombre' => 'BANCO SANTANDER DE NEGOCIOS CO', 'codigo_ach' => '1065'],
            ['nombre' => 'BANCO SERFINANZA S.A', 'codigo_ach' => '1069'],
            ['nombre' => 'BANCO UNION S.A', 'codigo_ach' => '1303'],
            ['nombre' => 'BANCO W S.A', 'codigo_ach' => '1053'],
            ['nombre' => 'BANCOLDEX S.A.', 'codigo_ach' => '1031'],
            ['nombre' => 'BANCOLOMBIA', 'codigo_ach' => '1007'],
            ['nombre' => 'BANCOOMEVA', 'codigo_ach' => '1061'],
            ['nombre' => 'BBVA COLOMBIA', 'codigo_ach' => '1013'],
            ['nombre' => 'BOLD CF', 'codigo_ach' => '1808'],
            ['nombre' => 'CITIBANK', 'codigo_ach' => '1009'],
            ['nombre' => 'COINK', 'codigo_ach' => '1812'],
            ['nombre' => 'COLTEFINANCIERA S.A', 'codigo_ach' => '1370'],
            ['nombre' => 'CONFIAR COOPERATIVA FINANCIERA', 'codigo_ach' => '1292'],
            ['nombre' => 'COOPERATIVA FINANCIERA DE ANTI', 'codigo_ach' => '1283'],
            ['nombre' => 'COOTRAFA COOPERATIVA FINANCIER', 'codigo_ach' => '1289'],
            ['nombre' => 'CREDIFAMILIA', 'codigo_ach' => '1117'],
            ['nombre' => 'CREZCAMOS S.A.', 'codigo_ach' => '1816'],
            ['nombre' => 'DAVIbank S.A.', 'codigo_ach' => '1019'],
            ['nombre' => 'DAVIPLATA', 'codigo_ach' => '1551'],
            ['nombre' => 'DING TECNIPAGOS SA', 'codigo_ach' => '1802'],
            ['nombre' => 'FINANCIERA JURISCOOP S.A. COMP', 'codigo_ach' => '1121'],
            ['nombre' => 'GLOBAL66', 'codigo_ach' => '1814'],
            ['nombre' => 'IRIS', 'codigo_ach' => '1637'],
            ['nombre' => 'ITAU', 'codigo_ach' => '1014'],
            ['nombre' => 'ITAU antes Corpbanca', 'codigo_ach' => '1006'],
            ['nombre' => 'JFK COOPERATIVA FINANCIERA', 'codigo_ach' => '1286'],
            ['nombre' => 'KOA C.F', 'codigo_ach' => '1807'],
            ['nombre' => 'LULO BANK S.A.', 'codigo_ach' => '1070'],
            ['nombre' => 'MIBANCO S.A.', 'codigo_ach' => '1067'],
            ['nombre' => 'MOVII', 'codigo_ach' => '1801'],
            ['nombre' => 'NEQUI', 'codigo_ach' => '1507'],
            ['nombre' => 'NU', 'codigo_ach' => '1809'],
            ['nombre' => 'PIBANK', 'codigo_ach' => '1560'],
            ['nombre' => 'POWWI', 'codigo_ach' => '1803'],
            ['nombre' => 'RAPPIPAY', 'codigo_ach' => '1811'],
            ['nombre' => 'UALÁ', 'codigo_ach' => '1804'],
        ];

        $todosBancosDb = Banco::all();

        foreach ($bancosACH as $bancoACH) {
            $nombreOriginal = strtolower(trim($bancoACH['nombre']));
            
            // Reemplazos para hacer match con los bancos que están nombrados ligeramente distinto
            $nombreSearch = str_replace(
                [' s.a.', ' s.a', ' sa.', ' sa', ' colombia', ' de colombia', 'banco '], 
                '', 
                $nombreOriginal
            );
            $nombreSearch = trim($nombreSearch);

            $bancoExistente = null;

            foreach ($todosBancosDb as $tb) {
                $tbNombre = strtolower(trim($tb->nombre));
                
                // Varias condiciones de coincidencia flexible
                if (
                    $tbNombre === $nombreOriginal || 
                    str_contains($nombreOriginal, $tbNombre) || 
                    str_contains($tbNombre, $nombreSearch)
                ) {
                    $bancoExistente = $tb;
                    break;
                }
            }

            if ($bancoExistente) {
                // Actualizar solo codigo_ach 
                $bancoExistente->update([
                    'codigo_ach' => $bancoACH['codigo_ach']
                ]);
            } else {
                // Insertar como nuevo, con nulls
                $nuevoBanco = Banco::create([
                    'nombre' => $bancoACH['nombre'],
                    'codigo_ach' => $bancoACH['codigo_ach'],
                    'direccion' => null,
                    'telefono' => null,
                ]);
                $todosBancosDb->push($nuevoBanco); // agregarlo para futuras iteraciones
            }
        }

        $this->command->info('✅ Bancos totales actualizados con ACH o insertados.');
    }
}