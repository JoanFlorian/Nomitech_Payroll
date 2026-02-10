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
            ]);
        }

        /* =========================
        EPS (Resolución DIAN 000013 de 2021)
        ========================== */
        $eps = [
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
            ]);
        }

        /* =========================
        AFP
        ========================== */
        $afp = [
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
            ]);
        }
    }
}
