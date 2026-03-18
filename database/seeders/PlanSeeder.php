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
                'nombre' => 'Pyme 10',
                'descripcion' => 'Ideal para microempresas que inician.',
                'num_empl' => 10,
                'max_admins' => 1,
                'max_auxiliares' => 0,
                'valor' => 150000,
                'duracion' => 90,
                'stripe_price_id' => 'price_1Syz6t5KUObx4PX3Ps3GKSx5',
                'destacado' => false,
                'orden' => 1,
                'features' => [
                    'Hasta 10 Empleados',
                    '1 Usuario Administrador',
                    '0 Auxiliares de Nómina',
                    'Plan por 3 meses',
                    'Soporte por Correo'
                ],
            ],
            [
                'nombre' => 'Pyme 30',
                'descripcion' => 'Para empresas pequeñas en crecimiento.',
                'num_empl' => 30,
                'max_admins' => 1,
                'max_auxiliares' => 2,
                'valor' => 405000,
                'duracion' => 90,
                'stripe_price_id' => 'price_1TCC5c5KUObx4PX3BDp2dSII',
                'destacado' => true,
                'orden' => 2,
                'features' => [
                    'Hasta 30 Empleados',
                    '1 Usuario Administrador',
                    '2 Auxiliares de Nómina',
                    'Plan por 3 meses',
                    'Soporte Prioritario'
                ],
            ],
            [
                'nombre' => 'Pyme 50',
                'descripcion' => 'Solución completa para medianas empresas.',
                'num_empl' => 50,
                'max_admins' => 2,
                'max_auxiliares' => 3,
                'valor' => 600000,
                'duracion' => 90,
                'stripe_price_id' => 'price_1TCCG45KUObx4PX3bwOq0amM',
                'destacado' => false,
                'orden' => 3,
                'features' => [
                    'Hasta 50 Empleados',
                    '2 Usuarios Administradores',
                    '3 Auxiliares de Nómina',
                    'Plan por 3 meses',
                    'Soporte WhatsApp'
                ],
            ],
            [
                'nombre' => 'Empresarial',
                'descripcion' => 'Solución escalable para grandes nóminas.',
                'num_empl' => 100,
                'max_admins' => 3,
                'max_auxiliares' => 5,
                'valor' => 1050000,
                'duracion' => 90,
                'stripe_price_id' => 'price_1TCCJy5KUObx4PX39pMAIpWU',
                'destacado' => false,
                'orden' => 4,
                'features' => [
                    'Hasta 100 Empleados',
                    '3 Usuarios Administradores',
                    '5 Auxiliares de Nómina',
                    'Plan por 3 meses',
                    'Account Manager'
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
