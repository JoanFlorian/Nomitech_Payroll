<?php
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // 1. Merge "Super amin" (5) and "Super admin" (8) into "Superadmin" (4)
    $targetSuperadmin = 4;
    $duplicatesSuperadmin = [5, 8];

    foreach ($duplicatesSuperadmin as $dupId) {
        $users = Usuario::where('id_rol', $dupId)->get();
        foreach ($users as $user) {
            $user->id_rol = $targetSuperadmin;
            $user->save();
            echo "User {$user->doc} moved from role $dupId to $targetSuperadmin\n";
        }
        
        // Move permissions if any?
        // Since they are duplicates, they might have specific permissions.
        // But for global roles, we want them to use the primary ID's permissions.
        
        DB::table('rol_permissions')->where('id_rol', $dupId)->delete();
        DB::table('rol')->where('id_rol', $dupId)->delete();
        echo "Role $dupId deleted.\n";
    }

    // 2. Fix names if needed
    $rolesToKeep = [
        1 => 'Representante Legal',
        2 => 'Administrador',
        3 => 'Empleado',
        4 => 'Superadmin',
        6 => 'Auxiliar de Nómina'
    ];

    foreach ($rolesToKeep as $id => $name) {
        Rol::where('id_rol', $id)->update(['nombre' => $name]);
    }

    DB::commit();
    echo "Cleanup finished successfully.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
