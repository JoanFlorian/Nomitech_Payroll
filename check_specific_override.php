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
    echo "PERMISSION_CLOSE_PERIOD_NOT_FOUND_IN_DB\n";
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
    echo "NO_OVERRIDE_FOR_CLOSE_PERIOD\n";
}
