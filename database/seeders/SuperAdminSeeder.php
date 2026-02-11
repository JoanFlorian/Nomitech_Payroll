<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\TipoDoc;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Superadmin role exists (id_rol = 4)
        DB::table('rol')->updateOrInsert(
            ['id_rol' => 4],
            ['nombre' => 'Superadmin', 'descripcion' => 'Administrator with full system access']
        );

        // 2. Get or create TipoDoc for Cedula
        $tipoDoc = TipoDoc::first() ?? TipoDoc::create([
            'id_tipo_doc' => 1,
            'nombre' => 'Cédula de Ciudadanía',
            'abreviatura' => 'CC'
        ]);

        // 3. Create SuperAdmin user
        $superAdmin = Usuario::firstOrCreate(
            ['doc' => '1070599004'],
            [
                'id_tipo_doc' => $tipoDoc->id_tipo_doc,
                'primer_nombre' => 'Super',
                'otros_nombres' => 'Admin',
                'primer_apellido' => 'System',
                'segundo_apellido' => 'Root',
                'correo' => 'superadmin@nomitech.local',
                'telefono' => '3182670119',
                'direccion' => 'Sistema',
                'contrasena' => Hash::make('SuperAdmin123!'),
                'id_rol' => 4,
                'activo' => true
            ]
        );

        $this->command->info('SuperAdmin user created/verified: ' . $superAdmin->correo);
    }
}
