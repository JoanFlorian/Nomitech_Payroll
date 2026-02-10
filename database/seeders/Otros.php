<?php

namespace Database\Seeders;

use App\Models\Afp;
use App\Models\Arl;
use App\Models\Eps;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\TipoCuenta;
use Illuminate\Database\Seeder;

class Otros extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        $departamentos = Departamento::pluck('id_departamento');

        for ($i = 1; $i <= 10; $i++) {
            Ciudad::insert([
                'nombre' => "user$i@test.com",
                'codigo' => "ABC$i",
                'id_departamento' => $departamentos->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }




        $sub_tipo_trabajador = [
            'Administrativo',
            'Operativo',
        ];

        foreach ($sub_tipo_trabajador as $value) {
            SubTipoTrabajador::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }


        $tipo_trabajador = [
            'Dependiente',
            'Independiente',

        ];

        foreach ($tipo_trabajador as $value) {
            TipoTrabajador::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tipo_contrato = [
            'Indefinido',
            'Fijo',
            'Prestacion de Servicios',

        ];

        foreach ($tipo_contrato as $value) {
            TipoContrato::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $arl = [
            'Colpatria',
            'Sura',
            'Bólivar',

        ];

        foreach ($arl as $value) {
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

        foreach ($forma_pago as $value) {
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

        foreach ($metodo_pago as $value) {
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

        foreach ($tipo_cuenta as $value) {
            TipoCuenta::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
=======
        /* =========================
        TIPOS DE CUENTA
        ========================== */
        $tipo_cuenta = [
            'Cuenta de Ahorros',
            'Cuenta Corriente',
            'Cuenta Simplificada',
        ];

        foreach ($tipo_cuenta as $value) {
            TipoCuenta::firstOrCreate([
                'nombre' => $value
>>>>>>> 0812a8f29b57e9060b8caab3aedaebae4866a702
            ]);
        }

        /* =========================
        EPS (Resolución DIAN 000013 de 2021)
        ========================== */
        $eps = [
<<<<<<< HEAD
            'Sura',
            'Sanitas',
            'Coomeva',
            'Salud Total',
            'Compensar',



        ];

        foreach ($eps as $value) {
            Eps::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
=======
            'Nueva EPS',
            'Coosalud',
            'Mutual Ser',
            'Salud Mía',
            'EPS Sura',
            'Sanitas EPS',
            'Salud Total',
            'Famisanar',
            'Compensar',
            'Aliansalud',
            'SOS',
            'Comfenalco Valle',
            'EPM EPS',
            'Fondo Pasivo Social FFCC',
            'Emssanar',
            'Capital Salud',
            'Savia Salud',
            'Asmet Salud',
            'Cajacopi',
            'Capresoca',
            'EPS Familiar',
            'Comfachocó',
            'Comfaoriente',
            'AIC EPSI',
            'Anas Wayuu EPSI',
            'Dusakawi EPSI',
            'Mallamas EPSI',
            'Pijaos Salud EPSI',
        ];

        foreach ($eps as $value) {
            Eps::firstOrCreate([
                'nombre' => $value
>>>>>>> 0812a8f29b57e9060b8caab3aedaebae4866a702
            ]);
        }

        /* =========================
        AFP
        ========================== */
        $afp = [
<<<<<<< HEAD
            'Protección',
            'Colfondos',
            'Porvenir',
            'Old Mutua',




        ];

        foreach ($afp as $value) {
            Afp::insert([
                'nombre' => $value,
                'created_at' => now(),
                'updated_at' => now(),
=======
            'Porvenir',
            'Protección',
            'Colfondos',
            'Skandia',
            'Colpensiones',
        ];

        foreach ($afp as $value) {
            Afp::firstOrCreate([
                'nombre' => $value
            ]);
        }

        /* =========================
        ARL
        ========================== */
        $arl = [
            'ARL SURA',
            'ARL Positiva',
            'ARL Colmena',
            'ARL AXA Colpatria',
            'ARL Bolívar',
            'ARL Allianz',
            'ARL La Equidad',
            'ARL Seguros de Vida Alfa',
        ];

        foreach ($arl as $value) {
            Arl::firstOrCreate([
                'nombre' => $value
            ]);
        }

        /* =========================
        FORMA DE PAGO
        ========================== */
        $forma_pago = [
            'Contado',
            'Crédito',
        ];

        foreach ($forma_pago as $value) {
            FormaPago::firstOrCreate([
                'nombre' => $value
            ]);
        }

        /* =========================
        MÉTODO DE PAGO
        ========================== */
        $metodo_pago = [
            '10 - Efectivo',
            '20 - Cheque',
            '30 - Transferencia Crédito',
            '42 - Consignación bancaria',
            '45 - Transferencia Crédito Bancario',
            '46 - Transferencia Débito Interbancario',
            '48 - Tarjeta Crédito',
            '49 - Tarjeta Débito',
            '98 - Cuentas Simplificada',
            'ZZZ - Acuerdo mutuo',
        ];

        foreach ($metodo_pago as $value) {
            MetodoPago::firstOrCreate([
                'nombre' => $value
>>>>>>> 0812a8f29b57e9060b8caab3aedaebae4866a702
            ]);
        }
    }
}
