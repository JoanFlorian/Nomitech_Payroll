<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EPSSeeder extends Seeder
{
    public function run(): void
    {
        $eps = [

            ['id_eps' => 9001562642, 'codigo_pila' => 'EPS010', 'nombre' => 'Nueva EPS'],
            ['id_eps' => 9002267153, 'codigo_pila' => 'EPS016', 'nombre' => 'Coosalud'],
            ['id_eps' => 8060083947, 'codigo_pila' => 'EPS048', 'nombre' => 'Mutual Ser'],
            ['id_eps' => 800100001, 'codigo_pila' => 'EPS999', 'nombre' => 'Salud Mía'],
            ['id_eps' => 800100002, 'codigo_pila' => 'EPS037', 'nombre' => 'EPS Sura'],
            ['id_eps' => 800100003, 'codigo_pila' => 'EPS005', 'nombre' => 'Sanitas EPS'],
            ['id_eps' => 800100004, 'codigo_pila' => 'EPS002', 'nombre' => 'Salud Total'],
            ['id_eps' => 800100005, 'codigo_pila' => 'EPS017', 'nombre' => 'Famisanar'],
            ['id_eps' => 800100006, 'codigo_pila' => 'EPS008', 'nombre' => 'Compensar'],
            ['id_eps' => 800100007, 'codigo_pila' => 'EPS012', 'nombre' => 'Aliansalud'],
            ['id_eps' => 800100008, 'codigo_pila' => 'EPS018', 'nombre' => 'SOS'],
            ['id_eps' => 800100009, 'codigo_pila' => 'EPS023', 'nombre' => 'Comfenalco Valle'],
            ['id_eps' => 800100010, 'codigo_pila' => 'EPS009', 'nombre' => 'EPM EPS'],
            ['id_eps' => 800100011, 'codigo_pila' => 'EPS998', 'nombre' => 'Fondo Pasivo Social FFCC'],
            ['id_eps' => 800100012, 'codigo_pila' => 'EPS022', 'nombre' => 'Emssanar'],
            ['id_eps' => 800100013, 'codigo_pila' => 'EPS034', 'nombre' => 'Capital Salud'],
            ['id_eps' => 800100014, 'codigo_pila' => 'EPS039', 'nombre' => 'Savia Salud'],
            ['id_eps' => 800100015, 'codigo_pila' => 'EPS046', 'nombre' => 'Asmet Salud'],
            ['id_eps' => 800100016, 'codigo_pila' => 'EPS050', 'nombre' => 'Cajacopi'],
            ['id_eps' => 800100017, 'codigo_pila' => 'EPS025', 'nombre' => 'Capresoca'],
            ['id_eps' => 800100018, 'codigo_pila' => 'EPS997', 'nombre' => 'EPS Familiar'],
            ['id_eps' => 800100019, 'codigo_pila' => 'EPS026', 'nombre' => 'Comfachocó'],
            ['id_eps' => 800100020, 'codigo_pila' => 'EPS027', 'nombre' => 'Comfaoriente'],
            ['id_eps' => 800100021, 'codigo_pila' => 'EPS040', 'nombre' => 'AIC EPSI'],
            ['id_eps' => 800100022, 'codigo_pila' => 'EPS041', 'nombre' => 'Anas Wayuu EPSI'],
            ['id_eps' => 800100023, 'codigo_pila' => 'EPS042', 'nombre' => 'Dusakawi EPSI'],
            ['id_eps' => 800100024, 'codigo_pila' => 'EPS043', 'nombre' => 'Mallamas EPSI'],
            ['id_eps' => 800100025, 'codigo_pila' => 'EPS044', 'nombre' => 'Pijaos Salud EPSI'],

        ];

        DB::table('eps')->upsert(
            $eps,
            ['id_eps'],
            ['codigo_pila','nombre']
        );

        echo "✅ EPS cargadas: " . count($eps) . " registros\n";
    }
}