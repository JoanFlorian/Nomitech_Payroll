<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener licencias existentes
        $licencias = DB::table('licencia')->pluck('id_licencia')->toArray();

        if (empty($licencias)) {
            echo "No hay licencias disponibles para crear pagos";
            return;
        }

        $pagos = [
            [
                'id_licencia' => $licencias[0] ?? 1,
                'id_metodo_pago' => 1,
                'valor' => 99.99,
                'estado' => 'ACTIVO',
                'descripcion' => 'Pago mensual Plan Básico',
            ],
            [
                'id_licencia' => $licencias[0] ?? 1,
                'id_metodo_pago' => 2,
                'valor' => 299.99,
                'estado' => 'ACTIVO',
                'descripcion' => 'Pago mensual Plan Profesional',
            ],
            [
                'id_licencia' => $licencias[0] ?? 1,
                'id_metodo_pago' => 3,
                'valor' => 799.99,
                'estado' => 'PENDIENTE',
                'descripcion' => 'Pago mensual Plan Enterprise',
            ],
        ];

        foreach ($pagos as $pago) {
            DB::table('pago')->insert([
                'id_licencia' => $pago['id_licencia'],
                'id_metodo_pago' => $pago['id_metodo_pago'],
                'valor' => $pago['valor'],
                'estado' => $pago['estado'],
                'descripcion' => $pago['descripcion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
