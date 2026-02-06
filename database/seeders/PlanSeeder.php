<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Ideal para pequeñas empresas que inician.',
                'valor' => 10000.00,
                'num_empl' => 5,
                'duracion' => 30, // 30 days
                'stripe_price_id' => 'price_1SxgM45KUObx4PX3qoitfezZ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Plan Estándar',
                'descripcion' => 'Para empresas en crecimiento con más empleados.',
                'valor' => 20000.00,
                'num_empl' => 15,
                'duracion' => 30,
                'stripe_price_id' => 'price_1SxgM45KUObx4PX3qoitfezZ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Plan Premium',
                'descripcion' => 'Solución completa para grandes nóminas.',
                'valor' => 30000.00,
                'num_empl' => 50,
                'duracion' => 30,
                'stripe_price_id' => 'price_1SxgM45KUObx4PX3qoitfezZ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
