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
                'id_caja' => 1,
                'nombre' => 'COMPENSAR',
                'empresa_nit' => '860066942',
                'codigo_pila' => 'CCF001',
                'telefono' => '018000915202',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 2,
                'nombre' => 'COLSUBSIDIO',
                'empresa_nit' => '860007336',
                'codigo_pila' => 'CCF002',
                'telefono' => '018000910500',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 3,
                'nombre' => 'CAFAM',
                'empresa_nit' => '860013570',
                'codigo_pila' => 'CCF003',
                'telefono' => '018000112700',
                'direccion' => 'Bogotá D.C.',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 4,
                'nombre' => 'COMFAMA',
                'empresa_nit' => '890900841',
                'codigo_pila' => 'CCF005',
                'telefono' => '018000415455',
                'direccion' => 'Medellín',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 5,
                'nombre' => 'COMFENALCO VALLE',
                'empresa_nit' => '890300001',
                'codigo_pila' => 'CCF006',
                'telefono' => '018000938585',
                'direccion' => 'Cali',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 6,
                'nombre' => 'COMFANDI',
                'empresa_nit' => '890300123',
                'codigo_pila' => 'CCF007',
                'telefono' => '018000930733',
                'direccion' => 'Cali',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 7,
                'nombre' => 'CAJACOPI',
                'empresa_nit' => '890102044',
                'codigo_pila' => 'CCF008',
                'telefono' => '018000912727',
                'direccion' => 'Barranquilla',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 8,
                'nombre' => 'COMFABOY',
                'empresa_nit' => '891800213',
                'codigo_pila' => 'CCF009',
                'telefono' => '018000915555',
                'direccion' => 'Tunja',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 9,
                'nombre' => 'COMFATOLIMA',
                'empresa_nit' => '890700148',
                'codigo_pila' => 'CCF017',
                'telefono' => '018000938585',
                'direccion' => 'Ibagué',
                'origen' => 'system',
                'estado' => 1
            ],

            [
                'id_caja' => 10,
                'nombre' => 'COMFENALCO TOLIMA',
                'empresa_nit' => '890700148',
                'codigo_pila' => 'CCF018',
                'telefono' => '018000938585',
                'direccion' => 'Ibagué',
                'origen' => 'system',
                'estado' => 1
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
