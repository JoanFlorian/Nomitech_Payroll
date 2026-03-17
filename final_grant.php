<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

$doc = '1111110';
$id_empresa = 6;

$user = Usuario::find($doc);
$p = Permission::where('name', 'close_period')->first();

if ($user && $p) {
    echo "SETTING ACTIVE = 1 FOR USER $doc COMPANY $id_empresa...\n";
    $user->directPermissions()->syncWithoutDetaching([
        $p->id => ['company_id' => $id_empresa, 'active' => 1]
    ]);
    
    // Clear cache globally for this user/company
    Cache::forget("permissions_user_{$doc}_company_{$id_empresa}");
    echo "CACHE CLEARED.\n";
}
