<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EPSSeeder extends Seeder
{
    public function run(): void
    {
        $eps = [

            ['id_eps' => 9001562642, 'codigo_pila' => 'EPS010', 'nombre' => 'Nueva EPS', 'telefono' => '018000585678', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 9002267153, 'codigo_pila' => 'EPS016', 'nombre' => 'Coosalud', 'telefono' => '018000589012', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 8060083947, 'codigo_pila' => 'EPS048', 'nombre' => 'Mutual Ser', 'telefono' => '018000581234', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100001, 'codigo_pila' => 'EPS999', 'nombre' => 'Salud Mía', 'telefono' => '018000525800', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100002, 'codigo_pila' => 'EPS037', 'nombre' => 'EPS Sura', 'telefono' => '018000510000', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100003, 'codigo_pila' => 'EPS005', 'nombre' => 'Sanitas EPS', 'telefono' => '018000516800', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100004, 'codigo_pila' => 'EPS002', 'nombre' => 'Salud Total', 'telefono' => '018000519191', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100005, 'codigo_pila' => 'EPS017', 'nombre' => 'Famisanar', 'telefono' => '018000183000', 'direccion' => 'Cali, Valle del Cauca', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100006, 'codigo_pila' => 'EPS008', 'nombre' => 'Compensar', 'telefono' => '018000518888', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100007, 'codigo_pila' => 'EPS012', 'nombre' => 'Aliansalud', 'telefono' => '018000934080', 'direccion' => 'Medellín, Antioquia', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100008, 'codigo_pila' => 'EPS018', 'nombre' => 'SOS', 'telefono' => '018000518500', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100009, 'codigo_pila' => 'EPS023', 'nombre' => 'Comfenalco Valle', 'telefono' => '018000512640', 'direccion' => 'Cali, Valle del Cauca', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100010, 'codigo_pila' => 'EPS009', 'nombre' => 'EPM EPS', 'telefono' => '018000511234', 'direccion' => 'Medellín, Antioquia', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100011, 'codigo_pila' => 'EPS998', 'nombre' => 'Fondo Pasivo Social FFCC', 'telefono' => '018000519876', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100012, 'codigo_pila' => 'EPS022', 'nombre' => 'Emssanar', 'telefono' => '018000516543', 'direccion' => 'Medellín, Antioquia', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100013, 'codigo_pila' => 'EPS034', 'nombre' => 'Capital Salud', 'telefono' => '018000527890', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100014, 'codigo_pila' => 'EPS039', 'nombre' => 'Savia Salud', 'telefono' => '018000534567', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100015, 'codigo_pila' => 'EPS046', 'nombre' => 'Asmet Salud', 'telefono' => '018000541234', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100016, 'codigo_pila' => 'EPS050', 'nombre' => 'Cajacopi', 'telefono' => '018000518901', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100017, 'codigo_pila' => 'EPS025', 'nombre' => 'Capresoca', 'telefono' => '018000527654', 'direccion' => 'Cauca', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100018, 'codigo_pila' => 'EPS997', 'nombre' => 'EPS Familiar', 'telefono' => '018000514321', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100019, 'codigo_pila' => 'EPS026', 'nombre' => 'Comfachocó', 'telefono' => '018000529876', 'direccion' => 'Quibdó, Chocó', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100020, 'codigo_pila' => 'EPS027', 'nombre' => 'Comfaoriente', 'telefono' => '018000516789', 'direccion' => 'Villavicencio, Meta', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100021, 'codigo_pila' => 'EPS040', 'nombre' => 'AIC EPSI', 'telefono' => '018000531234', 'direccion' => 'Bogotá D.C.', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100022, 'codigo_pila' => 'EPS041', 'nombre' => 'Anas Wayuu EPSI', 'telefono' => '018000545678', 'direccion' => 'La Guajira', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100023, 'codigo_pila' => 'EPS042', 'nombre' => 'Dusakawi EPSI', 'telefono' => '018000559012', 'direccion' => 'La Guajira', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100024, 'codigo_pila' => 'EPS043', 'nombre' => 'Mallamas EPSI', 'telefono' => '018000563456', 'direccion' => 'Nariño', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],
            ['id_eps' => 800100025, 'codigo_pila' => 'EPS044', 'nombre' => 'Pijaos Salud EPSI', 'telefono' => '018000577890', 'direccion' => 'Tolima', 'empresa_nit' => null, 'origen' => 'system', 'estado' => 1],

        ];

        DB::table('eps')->upsert(
            $eps,
            ['id_eps'],
            ['codigo_pila','nombre','telefono','direccion','empresa_nit','origen','estado']
        );

        echo "✅ EPS cargadas: " . count($eps) . " registros\n";
    }
}