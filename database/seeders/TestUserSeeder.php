<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Licencia;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Create or update test user
            $usuario = Usuario::updateOrCreate(
                ['doc' => '99999999'],
                [
                    'id_tipo_doc' => 1,
                    'contrasena' => Hash::make('password123'),
                    'primer_nombre' => 'Test',
                    'primer_apellido' => 'User',
                    'id_ciudad' => 1,
                    'direccion' => 'Calle Test 123',
                    'telefono' => '3000000000',
                    'correo' => 'test@nomitech.test',
                    'id_rol' => 1,
                    'activo' => 1,
                ]
            );

            // Create or update company
            $empresa = Empresa::updateOrCreate(
                ['nit' => '900000000'],
                [
                    'razon_social' => 'Test Company SAS',
                    'doc_representante' => $usuario->doc,
                    'id_ciudad' => 1,
                    'direccion' => 'Calle Empresa 1',
                    'telefono' => '3000000001',
                ]
            );

            // Attach via pivot if not exists
            $exists = DB::table('usuario_empresa')->where('doc', $usuario->doc)->where('id_empresa', $empresa->id_empresa)->exists();
            if (!$exists) {
                DB::table('usuario_empresa')->insert([
                    'doc' => $usuario->doc,
                    'id_empresa' => $empresa->id_empresa,
                ]);
            }

            // Ensure there is at least one plan
            $plan = Plan::first();
            if (!$plan) {
                $plan = Plan::create([
                    'nombre' => 'Seed Plan',
                    'descripcion' => 'Plan de prueba',
                    'valor' => 10000,
                    'num_empl' => 5,
                    'duracion' => 'mensual',
                    'stripe_price_id' => null,
                    'destacado' => false,
                    'orden' => 99,
                    'features' => ['Prueba']
                ]);
            }

            // Create active license for the company
            $licencia = Licencia::updateOrCreate(
                ['empresa_id' => $empresa->id_empresa],
                [
                    'plan_id' => $plan->id,
                    'fecha_inicio' => now()->subDays(1),
                    'fecha_fin' => now()->addDays(30),
                ]
            );
        });
    }
}
