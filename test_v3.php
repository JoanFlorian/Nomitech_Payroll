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

session(['empresa_id' => $id_empresa]);

$user = Usuario::find($doc);
$user->clearPermissionCache();

$has = $user->hasPermission('close_period');

$output = "RESULT: " . ($has ? 'GRANTED' : 'DENIED') . "\n";
$output .= "LIST: " . implode(',', Cache::get("permissions_user_{$doc}_company_{$id_empresa}") ?: []) . "\n";

file_put_contents('test_v3_result.txt', $output);
echo "DONE\n";
