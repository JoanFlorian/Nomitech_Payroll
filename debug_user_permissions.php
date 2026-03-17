<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use App\Models\Role;
use App\Models\Empresa;

$auxiliar = Usuario::whereHas('roles', function($q){
    $q->where('name', 'Auxiliar de Nómina');
})->first();

if (!$auxiliar) {
    echo "NO_AUXILIAR_FOUND\n";
    exit;
}

echo "USER_DOC:" . $auxiliar->doc . "\n";
echo "USER_NAME:" . $auxiliar->nombre_completo . "\n";

// Get first empresa
$empresa = $auxiliar->empresa()->first();
if (!$empresa) {
    echo "NO_EMPRESA_FOUND_FOR_USER\n";
    exit;
}

echo "EMPRESA_ID:" . $empresa->id_empresa . "\n";

// Set session for hasPermission
session(['empresa_id' => $empresa->id_empresa]);

echo "HAS_CLOSE_PERIOD:" . ($auxiliar->hasPermission('close_period') ? 'YES' : 'NO') . "\n";

// Dump current permissions list from cache logic
$id_empresa = $empresa->id_empresa;
$overrides = $auxiliar->directPermissions()
    ->where('company_id', $id_empresa)
    ->get()
    ->keyBy('name');

$roles = $auxiliar->roles()
    ->where('company_id', $id_empresa)
    ->with('permissions')
    ->get();

echo "ROLES_COUNT:" . $roles->count() . "\n";
foreach($roles as $role) {
    echo "ROLE_NAME:" . $role->name . " PERMS:" . implode(',', $role->permissions->pluck('name')->toArray()) . "\n";
}

echo "OVERRIDES_COUNT:" . $overrides->count() . "\n";
foreach($overrides as $name => $o) {
    echo "OVERRIDE:" . $name . " ACTIVE:" . $o->pivot->active . "\n";
}
