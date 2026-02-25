<?php

namespace Tests\Browser\Traits;

use App\Models\Rol;
use App\Models\Usuario;
use App\Models\TipoDoc;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

trait AuthenticatesSuperAdmin
{
    /**
     * Log in as a newly created or existing SuperAdmin.
     *
     * @param Browser $browser
     * @return Browser
     */
    protected function loginAsSuperAdmin(Browser $browser)
    {
        // Si ya estamos logueados en una página de superadmin, no volver a hacer login
        $currentUrl = $browser->driver->getCurrentURL();
        if (str_contains($currentUrl, '/superadmin')) {
            return $browser;
        }

        // Asegurar tipo de documento
        $tipoDoc = TipoDoc::firstOrCreate(['nombre' => 'Cédula de Ciudadanía']);

        // Asegurar que existan los roles con los IDs esperados por el LoginController
        // 1: Administrador, 2: Auxiliar RRHH, 3: Empleado, 4: SuperAdmin
        Rol::firstOrCreate(['id_rol' => 1], ['nombre' => 'Administrador']);
        Rol::firstOrCreate(['id_rol' => 2], ['nombre' => 'Auxiliar RRHH']);
        Rol::firstOrCreate(['id_rol' => 3], ['nombre' => 'Empleado']);

        $rolSuperAdmin = Rol::firstOrCreate(
            ['id_rol' => 4],
            ['nombre' => 'SuperAdmin', 'descripcion' => 'Rol de Administrador del Sistema']
        );

        // Crear usuario superadmin si no existe
        $superadmin = Usuario::firstOrCreate(
            ['correo' => 'admin@nomitech.com'],
            [
                'doc' => '12345678',
                'id_tipo_doc' => $tipoDoc->id_tipo_doc,
                'primer_nombre' => 'Super',
                'primer_apellido' => 'Admin',
                'direccion' => 'Calle Falsa 123',
                'telefono' => '1234567',
                'contrasena' => Hash::make('password123'),
                'id_rol' => $rolSuperAdmin->id_rol,
                'activo' => 1
            ]
        );

        return $browser->visit('/login')
            ->type('correo', 'admin@nomitech.com')
            ->type('contrasena', 'password123')
            ->press('Iniciar Sesion')
            ->waitForLocation('/superadmin/empresas', 10);
    }
}
