<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('usuario')->get();
        $permissions = DB::table('permissions')->get();

        foreach ($users as $user) {
            // Mark as owner if they were previously "Representante Legal"
            if ((int)$user->id_rol === 1) {
                DB::table('usuario')
                    ->where('doc', $user->doc)
                    ->update(['is_owner' => true]);
            }

            // For each company the user belongs to
            $userCompanies = DB::table('usuario_empresa')
                ->where('doc', $user->doc)
                ->get();

            foreach ($userCompanies as $uc) {
                // Ensure an "Administrador" role exists for this company
                $roleId = DB::table('roles')
                    ->where('company_id', $uc->id_empresa)
                    ->where('name', 'Administrador')
                    ->value('id');

                if (!$roleId) {
                    $roleId = DB::table('roles')->insertGetId([
                        'company_id' => $uc->id_empresa,
                        'name' => 'Administrador',
                        'description' => 'Acceso administrativo migrado del sistema anterior',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Assign all permissions to this new role
                    foreach ($permissions as $perm) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $perm->id,
                        ]);
                    }
                }

                // Associate user with the role if they had role 1 or 2
                if (in_array((int)$user->id_rol, [1, 2])) {
                    DB::table('user_roles')->updateOrInsert([
                        'user_id' => $user->doc,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_roles')->truncate();
        DB::table('role_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('usuario')->update(['is_owner' => false]);
    }
};
