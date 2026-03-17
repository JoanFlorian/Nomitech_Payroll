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

// Simular que el usuario quita el permiso (Denegar)
$p = Permission::where('name', 'close_period')->first();
$user = Usuario::find($doc);

if ($user && $p) {
    echo "SIMULATING REVOCATION (ACTIVE = 0)...\n";
    $user->directPermissions()->syncWithoutDetaching([
        $p->id => ['company_id' => $id_empresa, 'active' => 0]
    ]);
    $user->clearPermissionCache();
    
    // Set company in session for hasPermission
    session(['empresa_id' => $id_empresa]);
    
    echo "CHECKING PERMISSION...\n";
    $has = $user->hasPermission('close_period');
    echo "RESULT: " . ($has ? 'GRANTED' : 'DENIED') . "\n";
}
