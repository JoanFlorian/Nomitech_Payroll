<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

$user = Usuario::find('1111110');
$id_empresa = 6;

session(['empresa_id' => $id_empresa]);

if ($user) {
    // Forzar login
    auth()->login($user);
    
    echo "USER: " . $user->name . " (ID: 1111110)\n";
    echo "COMPANY_ID: $id_empresa\n";
    
    $can = Gate::allows('close_period');
    echo "GATE allows('close_period'): " . ($can ? 'YES' : 'NO') . "\n";
    
    $permissions = $user->hasPermission('close_period');
    echo "USER hasPermission('close_period'): " . ($permissions ? 'YES' : 'NO') . "\n";
} else {
    echo "USER_NOT_FOUND\n";
}
