<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$doc = '1111110';
$id_empresa = 6;

$output = "--- PERMISSION CHECK ---\n";
$rows = DB::table('user_permissions')
    ->where('user_id', $doc)
    ->where('company_id', $id_empresa)
    ->get();

foreach ($rows as $row) {
    $p = DB::table('permissions')->where('id', $row->permission_id)->first();
    $output .= "NAME: " . ($p->name ?? 'UNKNOWN') . " | ACTIVE: " . $row->active . "\n";
}
$output .= "--- END ---\n";

file_put_contents('perm_result.txt', $output);
echo "DONE\n";
