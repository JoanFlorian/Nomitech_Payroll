<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener licencias existentes (id y empresa)
        $licencias = DB::table('licencia')->select('id', 'empresa_id')->get();

        if ($licencias->isEmpty()) {
            echo "No hay licencias disponibles para crear pagos";
            return;
        }

        $licencia = $licencias->first();

        $pagos = [
            [
                'referencia' => 'PAY-STARTER-001',
                'empresa_id' => $licencia->empresa_id,
                'licencia_id' => $licencia->id,
                'valor' => 99.99,
                'moneda' => 'COP',
                'estado_pago' => 'paid',
                'proveedor_pago' => 'STRIPE',
            ],
            [
                'referencia' => 'PAY-PRO-001',
                'empresa_id' => $licencia->empresa_id,
                'licencia_id' => $licencia->id,
                'valor' => 299.99,
                'moneda' => 'COP',
                'estado_pago' => 'pending',
                'proveedor_pago' => 'STRIPE',
            ],
        ];

        foreach ($pagos as $pago) {
            DB::table('pago')->updateOrInsert(
                ['referencia' => $pago['referencia']],
                [
                    'empresa_id' => $pago['empresa_id'],
                    'licencia_id' => $pago['licencia_id'],
                    'valor' => $pago['valor'],
                    'moneda' => $pago['moneda'],
                    'estado_pago' => $pago['estado_pago'],
                    'proveedor_pago' => $pago['proveedor_pago'] ?? 'STRIPE',
                    'fecha_pago' => now(),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
