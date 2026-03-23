<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\Empresa;
use App\Models\Licencia;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'licencia_id' => Licencia::factory(),
            'plan_id' => Plan::first()?->id ?? 1,
            'referencia' => 'TEST-' . strtoupper(fake()->bothify('??#?#?')),
            'proveedor_pago' => 'Manual',
            'valor' => 150000,
            'moneda' => 'COP',
            'estado_pago' => 'aprobado',
            'fecha_pago' => now(),
        ];
    }
}
