<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Ciudad;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nit' => fake()->unique()->numerify('#########'),
            'nit_dv' => fake()->randomDigit(),
            'razon_social' => fake()->company(),
            'doc_representante' => Usuario::factory(),
            'id_ciudad' => Ciudad::first()->id_ciudad ?? Ciudad::create(['id_ciudad' => 11001, 'nombre' => 'Bogota', 'id_departamento' => 1])->id_ciudad,
            'direccion' => fake()->address(),
            'correo' => fake()->companyEmail(),
            'telefono' => fake()->phoneNumber(),
        ];
    }
}
