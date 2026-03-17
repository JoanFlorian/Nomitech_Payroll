<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$id_old = 10; // close_payroll
$id_new = 29; // close_period

DB::transaction(function() use ($id_old, $id_new) {
    // 1. Mover asignaciones de roles (si no existen ya en el destino)
    $role_perms = DB::table('role_permissions')->where('permission_id', $id_old)->get();
    foreach($role_perms as $rp) {
        $exists = DB::table('role_permissions')
            ->where('role_id', $rp->role_id)
            ->where('permission_id', $id_new)
            ->exists();
        if (!$exists) {
            DB::table('role_permissions')->insert([
                'role_id' => $rp->role_id,
                'permission_id' => $id_new
            ]);
        }
    }
    
    // 2. Mover asignaciones de usuarios
    $user_perms = DB::table('user_permissions')->where('permission_id', $id_old)->get();
    foreach($user_perms as $up) {
        $exists = DB::table('user_permissions')
            ->where('user_id', $up->user_id)
            ->where('company_id', $up->company_id)
            ->where('permission_id', $id_new)
            ->exists();
        if (!$exists) {
            DB::table('user_permissions')->insert([
                'user_id' => $up->user_id,
                'company_id' => $up->company_id,
                'permission_id' => $id_new,
                'active' => $up->active
            ]);
        }
    }
    
    // 3. Actualizar el permiso 29 para que esté en el módulo "Nómina" (donde lo busca el usuario)
    DB::table('permissions')->where('id', $id_new)->update([
        'module' => 'Nómina',
        'description' => 'Cerrar el periodo de nómina activo'
    ]);
    
    // 4. Eliminar el permiso viejo
    DB::table('role_permissions')->where('permission_id', $id_old)->delete();
    DB::table('user_permissions')->where('permission_id', $id_old)->delete();
    DB::table('permissions')->where('id', $id_old)->delete();
    
    echo "SUCCESS: Migrated and unified permissions.\n";
});

// Limpiar cache de todos los usuarios
\Illuminate\Support\Facades\Cache::flush();
echo "CACHE_FLUSHED\n";
