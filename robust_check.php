<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$doc = '1111110';
$id_empresa = 6;

echo "--- DATABASE CHECK FOR USER $doc COMPANY $id_empresa ---\n";

$rows = DB::table('user_permissions')
    ->where('user_id', $doc)
    ->where('company_id', $id_empresa)
    ->get();

foreach ($rows as $row) {
    $p = DB::table('permissions')->where('id', $row->permission_id)->first();
    echo "PERM_ID: " . $row->permission_id . " NAME: " . ($p->name ?? 'UNKNOWN') . " ACTIVE: " . $row->active . "\n";
}

$close_period_perm = DB::table('permissions')->where('name', 'close_period')->first();
if ($close_period_perm) {
    echo "CLOSE_PERIOD_ID: " . $close_period_perm->id . "\n";
} else {
    echo "CLOSE_PERIOD_PERM_NOT_FOUND_IN_DB\n";
}
echo "--- END CHECK ---\n";
