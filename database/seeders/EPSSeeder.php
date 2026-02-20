<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EPSSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('eps')->delete();

        DB::table('eps')->insert([
           ['id_eps' => 9001562642, 'nombre' => 'Nueva EPS', 'telefono' => '018000954400', 'direccion' => 'Av. Calle 26 # 69-76, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 9002267153, 'nombre' => 'Coosalud', 'telefono' => '018000515611', 'direccion' => 'Carrera 2A # 11-18, Bocagrande, Cartagena (Sede principal)'],
            ['id_eps' => 8060083947, 'nombre' => 'Mutual Ser', 'telefono' => '018000116882', 'direccion' => 'Calle 29 # 50-44, Cartagena (Sede principal)'],
            ['id_eps' => 800100001, 'nombre' => 'Salud Mía', 'telefono' => '018000000001', 'direccion' => 'Calle 100 # 7A-81, Bogotá D.C. (Oficina principal)'],
            ['id_eps' => 800100002, 'nombre' => 'EPS Sura', 'telefono' => '018000000002', 'direccion' => 'Calle 49 # 53-21, Medellín (Sede principal)'],
            ['id_eps' => 800100003, 'nombre' => 'Sanitas EPS', 'telefono' => '018000000003', 'direccion' => 'Av. Carrera 45 # 103-60, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100004, 'nombre' => 'Salud Total', 'telefono' => '018000000004', 'direccion' => 'Carrera 13 # 98-50, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100005, 'nombre' => 'Famisanar', 'telefono' => '018000000005', 'direccion' => 'Calle 100 # 11B-27, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100006, 'nombre' => 'Compensar', 'telefono' => '018000000006', 'direccion' => 'Av. 68 # 49A-47, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100007, 'nombre' => 'Aliansalud', 'telefono' => '018000000007', 'direccion' => 'Carrera 15 # 88-21, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100008, 'nombre' => 'SOS', 'telefono' => '018000000008', 'direccion' => 'Av. Roosevelt # 39-20, Cali (Sede principal)'],
            ['id_eps' => 800100009, 'nombre' => 'Comfenalco Valle', 'telefono' => '018000000009', 'direccion' => 'Calle 5 # 39-40, Cali (Sede principal)'],
            ['id_eps' => 800100010, 'nombre' => 'EPM EPS', 'telefono' => '018000000010', 'direccion' => 'Carrera 58 # 42-125, Medellín (Sede principal)'],
            ['id_eps' => 800100011, 'nombre' => 'Fondo Pasivo Social FFCC', 'telefono' => '018000000011', 'direccion' => 'Calle 26 # 68B-70, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100012, 'nombre' => 'Emssanar', 'telefono' => '018000000012', 'direccion' => 'Carrera 43 # 5B-77, Cali (Sede principal)'],
            ['id_eps' => 800100013, 'nombre' => 'Capital Salud', 'telefono' => '018000000013', 'direccion' => 'Calle 18 # 69B-50, Bogotá D.C. (Sede principal)'],
            ['id_eps' => 800100014, 'nombre' => 'Savia Salud', 'telefono' => '018000000014', 'direccion' => 'Carrera 48 # 19A-40, Medellín (Sede principal)'],
            ['id_eps' => 800100015, 'nombre' => 'Asmet Salud', 'telefono' => '018000000015', 'direccion' => 'Calle 10 # 23-60, Pasto (Sede principal)'],
            ['id_eps' => 800100016, 'nombre' => 'Cajacopi', 'telefono' => '018000000016', 'direccion' => 'Calle 72 # 43-52, Barranquilla (Sede principal)'],
            ['id_eps' => 800100017, 'nombre' => 'Capresoca', 'telefono' => '018000000017', 'direccion' => 'Calle 24 # 23-55, Yopal (Sede principal)'],
            ['id_eps' => 800100018, 'nombre' => 'EPS Familiar', 'telefono' => '018000000018', 'direccion' => 'Av. Libertadores # 12-34, Cúcuta (Sede principal)'],
            ['id_eps' => 800100019, 'nombre' => 'Comfachocó', 'telefono' => '018000000019', 'direccion' => 'Carrera 1 # 28-35, Quibdó (Sede principal)'],
            ['id_eps' => 800100020, 'nombre' => 'Comfaoriente', 'telefono' => '018000000020', 'direccion' => 'Av. 0 # 13-45, Cúcuta (Sede principal)'],
            ['id_eps' => 800100021, 'nombre' => 'AIC EPSI', 'telefono' => '018000000021', 'direccion' => 'Calle 10 # 6-40, Leticia (Sede principal)'],
            ['id_eps' => 800100022, 'nombre' => 'Anas Wayuu EPSI', 'telefono' => '018000000022', 'direccion' => 'Calle 14 # 7-25, Riohacha (Sede principal)'],
            ['id_eps' => 800100023, 'nombre' => 'Dusakawi EPSI', 'telefono' => '018000000023', 'direccion' => 'Calle 2 # 6-30, Valledupar (Sede principal)'],
            ['id_eps' => 800100024, 'nombre' => 'Mallamas EPSI', 'telefono' => '018000000024', 'direccion' => 'Calle 18 # 21-45, Pasto (Sede principal)'],
            ['id_eps' => 800100025, 'nombre' => 'Pijaos Salud EPSI', 'telefono' => '018000000025', 'direccion' => 'Carrera 5 # 15-60, Ibagué (Sede principal)'],
        ]);

       
    }
}
