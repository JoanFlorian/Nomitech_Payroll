<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schema = \Illuminate\Support\Facades\Schema::getColumnListing('user_permissions');
echo "COLUMNS: " . implode(', ', $schema) . "\n";

$duplicates = \Illuminate\Support\Facades\DB::table('user_permissions')
    ->select('user_id', 'permission_id', 'company_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
    ->groupBy('user_id', 'permission_id', 'company_id')
    ->having('count', '>', 1)
    ->get();

echo "DUPLICATES_COUNT: " . $duplicates->count() . "\n";
foreach($duplicates as $d) {
    echo "USER: {$d->user_id} PERM: {$d->permission_id} COMP: {$d->company_id} COUNT: {$d->count}\n";
}
