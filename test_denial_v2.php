<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

$doc = '1111110';
$id_empresa = 6;

$p = Permission::where('name', 'close_period')->first();
$user = Usuario::find($doc);

if ($user && $p) {
    echo "SETTING DENIAL (ACTIVE = 0)...\n";
    $user->directPermissions()->syncWithoutDetaching([
        $p->id => ['company_id' => $id_empresa, 'active' => 0]
    ]);
    
    // Set company FIRST
    session(['empresa_id' => $id_empresa]);
    
    // Clear cache NOW
    $user->clearPermissionCache();
    // Force clear just to be absolutely sure
    Cache::forget("permissions_user_{$doc}_company_{$id_empresa}");
    
    echo "CHECKING PERMISSION...\n";
    $has = $user->hasPermission('close_period');
    echo "RESULT: " . ($has ? 'GRANTED' : 'DENIED') . "\n";
    
    // Print all permissions returned
    $cacheKey = "permissions_user_{$doc}_company_{$id_empresa}";
    $list = Cache::get($cacheKey);
    echo "PERMISSION_LIST: " . implode(',', $list) . "\n";
}
