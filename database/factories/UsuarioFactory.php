<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\TipoDoc;
use App\Models\Rol;
use App\Models\Ciudad;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'doc' => fake()->unique()->numerify('##########'),
            'id_tipo_doc' => TipoDoc::first()->id_tipo_doc ?? TipoDoc::create(['id_tipo_doc' => 1, 'nombre' => 'Cedula de Ciudadania'])->id_tipo_doc,
            'primer_nombre' => fake()->firstName(),
            'otros_nombres' => fake()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'segundo_apellido' => fake()->lastName(),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'direccion' => fake()->address(),
            'id_ciudad' => Ciudad::first()->id_ciudad ?? Ciudad::create(['id_ciudad' => 11001, 'nombre' => 'Bogota', 'id_departamento' => 1])->id_ciudad,
            'id_rol' => Rol::first()->id_rol ?? Rol::create(['nombre' => 'Admin Role', 'descripcion' => 'Admin description'])->id_rol,
            'activo' => true,
            'contrasena' => Hash::make('password'),
            'is_owner' => false,
        ];
    }
}
