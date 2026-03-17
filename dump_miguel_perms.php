<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$doc = '1111110';
$id_empresa = 6;

$rows = DB::table('user_permissions')
    ->where('user_id', $doc)
    ->where('company_id', $id_empresa)
    ->get();

echo "USER_PERMISSIONS_DUMP:\n";
foreach ($rows as $row) {
    $p = DB::table('permissions')->where('id', $row->permission_id)->first();
    echo "ID: " . $row->permission_id . " NAME: " . ($p->name ?? 'UNKNOWN') . " ACTIVE: " . $row->active . "\n";
}
