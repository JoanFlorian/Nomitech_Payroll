<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

$doc = '1111110';
$id_empresa = 6;

$permission = Permission::where('name', 'close_period')->first();

if (!$permission) {
    echo "PERMISSION_NOT_FOUND\n";
    exit;
}

$user = Usuario::find($doc);
if (!$user) {
    echo "USER_NOT_FOUND\n";
    exit;
}

// Grant manually
$user->directPermissions()->syncWithoutDetaching([
    $permission->id => ['company_id' => $id_empresa, 'active' => 1]
]);

$user->clearPermissionCache();

echo "PERMISSION_GRANTED_SUCCESSFULLY_TO_MIGUEL_OROZCO\n";
