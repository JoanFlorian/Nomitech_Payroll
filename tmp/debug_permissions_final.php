<?php

use App\Models\Permission;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;

function debug($msg, $data = null) {
    echo "\n--- $msg ---\n";
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            print_r($data);
        } else {
            echo $data . "\n";
        }
    }
}

// 1. Check Permissions Table
$permissionCount = Permission::count();
debug("Total Permissions Count", $permissionCount);

if ($permissionCount > 0) {
    $samplePerm = Permission::first();
    debug("Sample Permission", $samplePerm->toArray());
    
    $modules = Permission::select('module_name')->distinct()->pluck('module_name');
    debug("Available Modules", $modules->toArray());
}

// 2. Check Usuario Primary Key
$userModel = new Usuario();
debug("Usuario Key Name", $userModel->getKeyName());

// 3. Find a sample user for testing
$sampleUser = Usuario::first();
if ($sampleUser) {
    debug("Testing with User", $sampleUser->doc);
    $id_usuario = $sampleUser->doc;
    
    // Simulate getPermissionsByEmployee logic
    $usuario = Usuario::find($id_usuario);
    if (!$usuario) {
        debug("ERROR: Could not find user with ID $id_usuario");
    } else {
        $permissions = Permission::all();
        $userOverrides = UserPermission::where('id_usuario', $id_usuario)
            ->where('id_empresa', 6) // Hardcoded 6 for testing based on summary
            ->get()
            ->keyBy('permission_id');
            
        $rolePermissions = DB::table('rol_permissions')
            ->where('id_rol', $usuario->id_rol)
            ->pluck('permission_id')
            ->toArray();
            
        $grouped = $permissions->groupBy('module_name');
        $response = [];
        foreach ($grouped as $moduleName => $perms) {
            $modulePerms = [];
            foreach ($perms as $p) {
                $override = $userOverrides->get($p->id);
                $isInherited = in_array($p->id, $rolePermissions);
                $active = isset($override) ? (bool)$override->active : $isInherited;
                
                $modulePerms[] = [
                    'id' => $p->id,
                    'name' => $p->display_name ?? $p->name,
                    'inherited' => $isInherited,
                    'has_override' => isset($override),
                    'active' => $active
                ];
            }
            $response[] = [
                'module' => $moduleName,
                'permissions' => $modulePerms
            ];
        }
        
        debug("Sample Response structure (first 2 modules)", array_slice($response, 0, 2));
        debug("Total modules in response", count($response));
    }
} else {
    debug("ERROR: No users found in database");
}
