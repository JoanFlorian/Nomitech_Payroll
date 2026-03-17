<?php
use App\Models\Usuario;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "--- RBAC Verification Start ---\n";

    // 1. Check if permissions were seeded
    $permCount = Permission::count();
    echo "Permissions seeded: $permCount\n";
    if ($permCount == 0) throw new Exception("Permissions not seeded!");

    // 2. Check a migrated user
    $owner = Usuario::where('is_owner', true)->first();
    if ($owner) {
        echo "Found owner: {$owner->correo}\n";
        echo "Owner has view_employees permission: " . ($owner->hasPermission('view_employees') ? 'YES' : 'NO') . "\n";
        echo "Owner bypass works for unknown permission: " . ($owner->hasPermission('magic_perm') ? 'YES' : 'NO') . "\n";
    } else {
        echo "No owners found (might be a fresh DB).\n";
    }

    // 3. Check role-permission relationship
    $adminRole = Role::where('name', 'Administrador')->first();
    if ($adminRole) {
        echo "Found Administrador role for company: {$adminRole->company_id}\n";
        echo "Role has permissions count: " . $adminRole->permissions()->count() . "\n";
    }

    // 4. Check user-role relationship
    $rolesCount = DB::table('user_roles')->count();
    echo "Total user_roles associations: $rolesCount\n";

    echo "--- RBAC Verification Success ---\n";
} catch (\Exception $e) {
    echo "VERIFICATION FAILED: " . $e->getMessage() . "\n";
}
