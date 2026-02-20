<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LicenciaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener empresas existentes (mínimo 1 debe existir)
        $empresas = DB::table('empresa')->pluck('id_empresa')->toArray();

        if (empty($empresas)) {
            echo "No hay empresas disponibles para crear licencias";
            return;
        }

        $licencias = [
            [
                'empresa_id' => $empresas[0] ?? 1,
                'plan_id' => 1,
                'fecha_inicio' => Carbon::now()->subMonths(6),
                'fecha_fin' => Carbon::now()->addMonths(6),
            ],
            [
                'empresa_id' => $empresas[0] ?? 1,
                'plan_id' => 2,
                'fecha_inicio' => Carbon::now()->subMonths(3),
                'fecha_fin' => Carbon::now()->addMonths(9),
            ],
            [
                'empresa_id' => $empresas[0] ?? 1,
                'plan_id' => 3,
                'fecha_inicio' => Carbon::now(),
                'fecha_fin' => Carbon::now()->addMonths(12),
            ],
        ];

        foreach ($licencias as $licencia) {
            DB::table('licencia')->updateOrInsert(
                [
                    'empresa_id' => $licencia['empresa_id'],
                    'plan_id' => $licencia['plan_id'],
                ],
                [
                    'fecha_inicio' => $licencia['fecha_inicio'],
                    'fecha_fin' => $licencia['fecha_fin'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
