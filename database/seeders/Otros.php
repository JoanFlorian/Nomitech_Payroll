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
        // ARL con mayor cobertura nacional
        'ARL SURA',
        'ARL Positiva',
        'ARL Colmena',

        // ARL privadas con alta presencia
        'ARL AXA Colpatria',
        'ARL Bolívar',
        'ARL Allianz',

        // ARL con cobertura más focalizada
        'ARL La Equidad',
        'ARL Seguros de Vida Alfa',
        
        ];

        foreach ($arl as $value){
            Arl::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $forma_pago = [
        'Contado',
        'Crédito',
        
        ];

        foreach ($forma_pago as $value){
            FormaPago::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $metodo_pago = [
        '1 - Instrumento no definido',
        '2 - Crédito ACH',
        '3 - Débito ACH',
        '4 - Reversión débito de demanda ACH',
        '5 - Reversión crédito de demanda ACH',
        '6 - Crédito de demanda ACH',
        '7 - Débito de demanda ACH',
        '8 - Mantener',
        '9 - Clearing Nacional o Regional',
        '10 - Efectivo',
        '11 - Reversión Crédito Ahorro',
        '12 - Reversión Débito Ahorro',
        '13 - Crédito Ahorro',
        '14 - Débito Ahorro',
        '15 - Bookentry Crédito',
        '16 - Bookentry Débito',
        '17 - Concentración de la demanda en efectivo / Desembolso Crédito (CCD)',
        '18 - Concentración de la demanda en efectivo / Desembolso Débito (CCD)',
        '19 - Crédito Pago negocio corporativo (CTP)',
        '20 - Cheque',
        '21 - Proyecto bancario',
        '22 - Proyecto bancario certificado',
        '23 - Cheque bancario',
        '24 - Nota cambiaria esperando aceptación',
        '25 - Cheque certificado',
        '26 - Cheque Local',
        '27 - Débito Pago Negocio Corporativo (CTP)',
        '28 - Crédito Negocio Intercambio Corporativo (CTX)',
        '29 - Débito Negocio Intercambio Corporativo (CTX)',
        '30 - Transferencia Crédito',
        '31 - Transferencia Débito',
        '32 - Concentración Efectivo / Desembolso Crédito plus (CCD+)',
        '33 - Concentración Efectivo / Desembolso Débito plus (CCD+)',
        '34 - Pago y depósito pre acordado (PPD)',
        '35 - Concentración efectivo ahorros / Desembolso Crédito (CCD)',
        '36 - Concentración efectivo ahorros / Desembolso Débito (CCD)',
        '37 - Pago Negocio Corporativo Ahorros Crédito (CTP)',
        '38 - Pago Negocio Corporativo Ahorros Débito (CTP)',
        '39 - Crédito Negocio Intercambio Corporativo (CTX)',
        '40 - Débito Negocio Intercambio Corporativo (CTX)',
        '41 - Concentración efectivo / Desembolso Crédito plus (CCD+)',
        '42 - Consignación bancaria',
        '43 - Concentración efectivo / Desembolso Débito plus (CCD+)',
        '44 - Nota cambiaria',
        '45 - Transferencia Crédito Bancario',
        '46 - Transferencia Débito Interbancario',
        '47 - Transferencia Débito Bancaria',
        '48 - Tarjeta Crédito',
        '49 - Tarjeta Débito',
        '50 - Postgiro',
        '51 - Telex estándar bancario francés',
        '52 - Pago comercial urgente',
        '53 - Pago Tesorería Urgente',
        '60 - Nota promisoria',
        '61 - Nota promisoria firmada por el acreedor',
        '62 - Nota promisoria firmada por el acreedor, avalada por el banco',
        '63 - Nota promisoria firmada por el acreedor, avalada por un tercero',
        '64 - Nota promisoria firmada por el banco',
        '65 - Nota promisoria firmada por un banco avalada por otro banco',
        '66 - Nota promisoria firmada',
        '67 - Nota promisoria firmada por un tercero avalada por un banco',
        '70 - Retiro de nota por el acreedor',
        '71 - Bonos',
        '72 - Vales',
        '74 - Retiro de nota por el acreedor sobre un banco',
        '75 - Retiro de nota por el acreedor, avalada por otro banco',
        '76 - Retiro de nota por el acreedor, sobre un banco avalada por un tercero',
        '77 - Retiro de una nota por el acreedor sobre un tercero',
        '78 - Retiro de una nota por el acreedor sobre un tercero avalada por un banco',
        '91 - Nota bancaria transferible',
        '92 - Cheque local transferible',
        '93 - Giro referenciado',
        '94 - Giro urgente',
        '95 - Giro formato abierto',
        '96 - Método de pago solicitado no usado',
        '97 - Clearing entre partners',
        '98 - Cuentas de Ahorro de Trámite Simplificado (CATS) (Nequi, Daviplata, etc)',
        'ZZZ - Acuerdo mutuo',
        
        
        ];

        foreach ($metodo_pago as $value){
            MetodoPago::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $tipo_cuenta = [
        // Tipos de cuenta bancarios
        'Cuenta de Ahorros',
        'Cuenta Corriente',

        // Cuentas simplificadas (válidas según regulación financiera)
        'Cuenta de Ahorro de Trámite Simplificado (CATS)',
        
        
        ];

        foreach ($tipo_cuenta as $value){
            TipoCuenta::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $eps = [
         // EPS con mayor cobertura nacional (Contributivo + Subsidiado)
        'Nueva EPS',
        'Coosalud',
        'Mutual Ser',
        'Salud Mía',

        // EPS régimen contributivo (alta presencia)
        'EPS Sura',
        'EPS Sanitas',
        'Salud Total',
        'Famisanar',
        'Compensar',
        'Aliansalud',
        'Servicio Occidental de Salud (SOS)',
        'Comfenalco Valle',
        'Empresas Públicas de Medellín (EPM)',
        'Fondo de Pasivo Social de Ferrocarriles Nacionales de Colombia',

        // EPS régimen subsidiado (cobertura regional y poblacional)
        'Emssanar',
        'Capital Salud',
        'Savia Salud',
        'Asmet Salud',
        'Cajacopi Atlántico',
        'Capresoca',
        'EPS Familiar de Colombia',
        'Comfachocó',
        'Comfaoriente',

        // EPS Indígenas (EPSI)
        'Asociación Indígena del Cauca EPSI',
        'Anas Wayuu EPSI',
        'Dusakawi EPSI',
        'Mallamas EPSI',
        'Pijaos Salud EPSI',
        
        
        
        ];

        foreach ($eps as $value){
            Eps::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $afp = [
        // AFP privadas (mayor número de afiliados)
        'Porvenir',
        'Protección',
        'Colfondos',
        'Skandia',

        // Régimen de Prima Media (público)
        'Colpensiones',
        
        
        
        
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
