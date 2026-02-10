<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Plan básico de nómina para pequeñas empresas',
                'valor' => 99.99,
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Profesional',
                'descripcion' => 'Plan profesional con características avanzadas',
                'valor' => 299.99,
                'activo' => true,
            ],
            [
                'nombre' => 'Plan Enterprise',
                'descripcion' => 'Plan enterprise con soporte dedicado',
                'valor' => 799.99,
                'activo' => true,
            ],
        ];

        foreach ($planes as $plan) {
            DB::table('plan')->insert([
                'nombre' => $plan['nombre'],
                'descripcion' => $plan['descripcion'],
                'valor' => $plan['valor'],
                'activo' => $plan['activo'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
