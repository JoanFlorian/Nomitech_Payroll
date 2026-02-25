<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
                'nombre' => 'Starter',
                'descripcion' => 'Ideal para pequeñas empresas que inician.',
                'num_empl' => 15,
                'valor' => 15 * 3000,
                'duracion' => 30,
                'stripe_price_id' => 'price_1Syz1O5KUObx4PX3yChoOuTO',
                'destacado' => false,
                'orden' => 1,
                'features' => [
                    'Soporta hasta 15 Empleados',
                    'Gestión Básica de Nómina Electrónica',
                    'Liquidación mensual y quincenal',
                    'Soporte estándar por correo',
                    'Generación de comprobantes PDF'
                ],
            ],
            [
                'nombre' => 'Pro',
                'descripcion' => 'Para empresas en crecimiento con más empleados.',
                'num_empl' => 30,
                'valor' => 30 * 3000,
                'duracion' => 30,
                'stripe_price_id' => 'price_1Syz4H5KUObx4PX302wa6cKL',
                'destacado' => true,
                'orden' => 2,
                'features' => [
                    'Soporta hasta 30 Empleados',
                    'Todo lo del plan Starter',
                    'Integración contable básica',
                    'Atención prioritaria por chat',
                    'Acceso multiusuario (hasta 3 admins)'
                ],
            ],
            [
                'nombre' => 'Pyme',
                'descripcion' => 'Solución completa para medianas empresas.',
                'num_empl' => 50,
                'valor' => 50 * 3000,
                'duracion' => 30,
                'stripe_price_id' => 'price_1Syz6t5KUObx4PX3Ps3GKSx5',
                'destacado' => false,
                'orden' => 3,
                'features' => [
                    'Soporta hasta 50 Empleados',
                    'Todo lo del plan Pro',
                    'Reportes Avanzados de Costos',
                    'Portal de autoservicio para empleados',
                    'Soporte telefónico directo'
                ],
            ],
            [
                'nombre' => 'Enterprise',
                'descripcion' => 'Solución empresarial para grandes nóminas.',
                'num_empl' => 100,
                'valor' => 100 * 3000,
                'duracion' => 30,
                'stripe_price_id' => 'price_1Syz9N5KUObx4PX3sI5rBpcL',
                'destacado' => false,
                'orden' => 4,
                'features' => [
                    'Soporta hasta 100 Empleados',
                    'Todo lo del plan Pyme',
                    'SLA de disponibilidad garantizada',
                    'Desarrollo de integraciones a medida',
                    'Account Manager dedicado'
                ],
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['nombre' => $planData['nombre']],
                $planData
            );
        }
    }
}
