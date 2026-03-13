<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CajaCompensacion;

class CajaCompensacionSeeder extends Seeder
{
    public function run(): void
    {
        $cajas = [

            [
                'id_caja' => 860066942,
                'nombre' => 'COMPENSAR',
                'codigo_pila' => 'CCF001',
                'telefono' => '018000915202',
                'direccion' => 'Bogotá D.C.',
            ],

            [
                'id_caja' => 860007336,
                'nombre' => 'COLSUBSIDIO',
                'codigo_pila' => 'CCF002',
                'telefono' => '018000910500',
                'direccion' => 'Bogotá D.C.',
            ],

            [
                'id_caja' => 860013570,
                'nombre' => 'CAFAM',
                'codigo_pila' => 'CCF003',
                'telefono' => '018000112700',
                'direccion' => 'Bogotá D.C.',
            ],

            [
                'id_caja' => 860014841,
                'nombre' => 'COMFAMA',
                'codigo_pila' => 'CCF005',
                'telefono' => '018000415455',
                'direccion' => 'Medellín',
            ],

            [
                'id_caja' => 860015000,
                'nombre' => 'COMFENALCO VALLE',
                'codigo_pila' => 'CCF006',
                'telefono' => '018000938585',
                'direccion' => 'Cali',
            ],

            [
                'id_caja' => 860015001,
                'nombre' => 'COMFANDI',
                'codigo_pila' => 'CCF007',
                'telefono' => '018000930733',
                'direccion' => 'Cali',
            ],

            [
                'id_caja' => 890102044,
                'nombre' => 'CAJACOPI',
                'codigo_pila' => 'CCF008',
                'telefono' => '018000912727',
                'direccion' => 'Barranquilla',
            ],

            [
                'id_caja' => 891800213,
                'nombre' => 'COMFABOY',
                'codigo_pila' => 'CCF009',
                'telefono' => '018000915555',
                'direccion' => 'Tunja',
            ],

            [
                'id_caja' => 890700148,
                'nombre' => 'COMFATOLIMA',
                'codigo_pila' => 'CCF017',
                'telefono' => '018000938585',
                'direccion' => 'Ibagué',
            ],

            [
                'id_caja' => 890700149,
                'nombre' => 'COMFENALCO TOLIMA',
                'codigo_pila' => 'CCF018',
                'telefono' => '018000938585',
                'direccion' => 'Ibagué',
            ],

        ];

        foreach ($cajas as $caja) {
            CajaCompensacion::updateOrCreate(
                ['id_caja' => $caja['id_caja']],
                $caja
            );
        }

        $this->command->info('✅ Cajas de compensación cargadas: ' . count($cajas) . ' registros');
    }
}
