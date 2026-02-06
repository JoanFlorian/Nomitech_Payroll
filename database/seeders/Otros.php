<?php

namespace Database\Seeders;

use App\Models\Afp;
use App\Models\Arl;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Eps;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\SubTipoTrabajador;
use App\Models\TipoContrato;
use App\Models\TipoCuenta;
use App\Models\TipoDoc;
use App\Models\TipoTrabajador;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Otros extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $departamentos = Departamento::pluck('id_departamento');
        
        // for ($i = 1; $i <= 10; $i++) {
        //     Ciudad::insert([
        //         'nombre' => "user$i@test.com",
        //         'codigo' => "ABC$i",
        //         'id_departamento' => $departamentos->random(),
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        
        // }


        // $tipo_doc = [
        //     'CC' => 'Cédula de Ciudadanía',
        //     'CE' => 'Cedula de Extrangeria',
        //     'TI' => 'Tarjeta de identidad'
        // ];

        // foreach ($tipo_doc as $key => $value){
        //     TipoDoc::insert([
        //         'nombre' => $value,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // $sub_tipo_trabajador = [
        // 'Administrativo',
        // 'Operativo',
        // ];

        // foreach ($sub_tipo_trabajador as $value){
        //     SubTipoTrabajador::insert([
        //         'nombre' => $value,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }


        // $tipo_trabajador = [
        // 'Dependiente',
        // 'Independiente',
        
        // ];

        // foreach ($tipo_trabajador as $value){
        //     TipoTrabajador::insert([
        //         'nombre' => $value,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        // $tipo_contrato = [
        // 'Indefinido',
        // 'Fijo',
        // 'Prestacion de Servicios',
        
        // ];

        // foreach ($tipo_contrato as $value){
        //     TipoContrato::insert([
        //         'nombre' => $value,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        $arl = [
        'Colpatria',
        'Sura',
        'Bólivar',
        
        ];

        foreach ($arl as $value){
            Arl::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $forma_pago = [
        'Mensual',
        'Quincenal',
        'Semanal',
        
        ];

        foreach ($forma_pago as $value){
            FormaPago::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $metodo_pago = [
        'Transferencia',
        'Cheque',
        'Efectivo',
        
        
        ];

        foreach ($metodo_pago as $value){
            MetodoPago::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $tipo_cuenta = [
        'Ahorros',
        'Corriente',
        
        
        ];

        foreach ($tipo_cuenta as $value){
            TipoCuenta::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $eps = [
        'Sura',
        'Sanitas',
        'Coomeva',
        'Salud Total',
        'Compensar',
        
        
        
        ];

        foreach ($eps as $value){
            Eps::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $afp = [
        'Protección',
        'Colfondos',
        'Porvenir',
        'Old Mutua',
        
        
        
        
        ];

        foreach ($afp as $value){
            Afp::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

}
