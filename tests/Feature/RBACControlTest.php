<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RBACControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_owner_has_all_permissions()
    {
        $owner = Usuario::factory()->create(['is_owner' => true]);
        
        $this->assertTrue($owner->hasPermission('view_employees'));
        $this->assertTrue($owner->hasPermission('non_existent_permission'));
    }

    public function test_user_with_role_has_specific_permissions()
    {
        $empresa = Empresa::factory()->create();
        $user = Usuario::factory()->create(['is_owner' => false]);
        $user->empresa()->attach($empresa->id_empresa);

        $role = Role::create([
            'company_id' => $empresa->id_empresa,
            'name' => 'Tester',
        ]);

        $permission = Permission::where('name', 'view_employees')->first();
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);

        session(['empresa_id' => $empresa->id_empresa]);

        $this->assertTrue($user->hasPermission('view_employees'));
        $this->assertFalse($user->hasPermission('close_period'));
    }

    public function test_roles_are_scoped_by_company()
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $roleA = Role::create(['company_id' => $empresaA->id_empresa, 'name' => 'Admin']);
        $roleB = Role::create(['company_id' => $empresaB->id_empresa, 'name' => 'Admin']);

        $user = Usuario::factory()->create(['is_owner' => false]);
        $user->empresa()->attach($empresaA->id_empresa);
        $user->roles()->attach($roleA->id);

        // Even if role names are same, they are different IDs and scoped.
        $this->assertTrue($user->roles->contains($roleA));
        $this->assertFalse($user->roles->contains($roleB));
        
        // Test new strict scoping in hasPermission
        $permission = Permission::where('name', 'view_employees')->first();
        $roleA->permissions()->attach($permission->id);
        
        session(['empresa_id' => $empresaA->id_empresa]);
        $this->assertTrue($user->hasPermission('view_employees'));

        session(['empresa_id' => $empresaB->id_empresa]);
        $this->assertFalse($user->hasPermission('view_employees'));
    }
}
