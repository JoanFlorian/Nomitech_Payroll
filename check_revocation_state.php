<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

$doc = '1111110';
$id_empresa = 6;

$permission = DB::table('permissions')->where('name', 'close_period')->first();
if (!$permission) {
    echo "PERMISSION_NOT_FOUND\n";
    exit;
}

$override = DB::table('user_permissions')
    ->where('user_id', $doc)
    ->where('company_id', $id_empresa)
    ->where('permission_id', $permission->id)
    ->first();

if ($override) {
    echo "OVERRIDE_FOUND: ACTIVE=" . $override->active . "\n";
} else {
    echo "NO_OVERRIDE_FOUND\n";
}

// Also check Role 19 (Miguel's role) just in case it was accidentally given the permission globally
$role_perm = DB::table('role_permissions')
    ->where('role_id', 19)
    ->where('permission_id', $permission->id)
    ->first();

echo "ROLE_19_HAS_PERM: " . ($role_perm ? 'YES' : 'NO') . "\n";

// Check if there is active cache
$cacheKey = "permissions_user_{$doc}_company_{$id_empresa}";
$cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
if ($cached) {
    echo "CACHE_FOUND: " . implode(',', $cached) . "\n";
} else {
    echo "CACHE_NOT_FOUND\n";
}
