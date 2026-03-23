<?php

namespace Database\Factories;

use App\Models\Licencia;
use App\Models\Empresa;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenciaFactory extends Factory
{
    protected $model = Licencia::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'plan_id' => Plan::first()?->id ?? 1,
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonths(3),
        ];
    }
}
