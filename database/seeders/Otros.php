<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Afp;
use App\Models\Arl;
use App\Models\Eps;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\TipoCuenta;

class Otros extends Seeder
{
    public function run(): void
    {
        /* =========================
        TIPOS DE CUENTA
        ========================== */
        $tipoCuenta = [
            'Cuenta de Ahorros',
            'Cuenta Corriente',
            'Cuenta Simplificada',
        ];

        foreach ($tipoCuenta as $value) {
            TipoCuenta::firstOrCreate([
                'nombre' => $value
            ]);
        }

        /* =========================
        EPS
        ========================== */
        $eps = [
            'Nueva EPS',
            'Coosalud',
            'Mutual Ser',
            'EPS Sura',
            'Sanitas EPS',
            'Salud Total',
            'Famisanar',
            'Compensar',
            'Aliansalud',
            'Capital Salud',
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
        ];

        foreach ($arl as $value) {
            Arl::firstOrCreate([
                'nombre' => $value
            ]);
        }

        /* =========================
        FORMA DE PAGO
        ========================== */
        $formaPago = [
            'Contado',
            'Crédito',
        ];

        foreach ($formaPago as $value) {
            FormaPago::firstOrCreate([
                'nombre' => $value
            ]);
        }

        /* =========================
        MÉTODO DE PAGO
        ========================== */
        $metodoPago = [
            '10 - Efectivo',
            '20 - Cheque',
            '30 - Transferencia Crédito',
            '48 - Tarjeta Crédito',
            '49 - Tarjeta Débito',
        ];

        foreach ($metodoPago as $value) {
            MetodoPago::firstOrCreate([
                'nombre' => $value
            ]);
        }
    }
}
