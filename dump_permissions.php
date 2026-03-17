<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$permissions = \App\Models\Permission::pluck('name')->toArray();
echo "PERMISSIONS_START:" . implode(',', $permissions) . ":PERMISSIONS_END";
