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
                'id_empresa' => $empresas[0] ?? 1,
                'id_plan' => 1,
                'estado' => 'ACTIVA',
                'fecha_inicio' => Carbon::now()->subMonths(6),
                'fecha_fin' => Carbon::now()->addMonths(6),
                'vigencia_dias' => 365,
            ],
            [
                'id_empresa' => $empresas[0] ?? 1,
                'id_plan' => 2,
                'estado' => 'ACTIVA',
                'fecha_inicio' => Carbon::now()->subMonths(3),
                'fecha_fin' => Carbon::now()->addMonths(9),
                'vigencia_dias' => 365,
            ],
            [
                'id_empresa' => $empresas[0] ?? 1,
                'id_plan' => 3,
                'estado' => 'PENDIENTE',
                'fecha_inicio' => Carbon::now(),
                'fecha_fin' => Carbon::now()->addMonths(12),
                'vigencia_dias' => 365,
            ],
        ];

        foreach ($licencias as $licencia) {
            DB::table('licencia')->insert([
                'id_empresa' => $licencia['id_empresa'],
                'id_plan' => $licencia['id_plan'],
                'estado' => $licencia['estado'],
                'fecha_inicio' => $licencia['fecha_inicio'],
                'fecha_fin' => $licencia['fecha_fin'],
                'vigencia_dias' => $licencia['vigencia_dias'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
