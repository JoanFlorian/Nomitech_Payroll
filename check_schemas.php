<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schema_ur = \Illuminate\Support\Facades\Schema::getColumnListing('user_roles');
echo "USER_ROLES: " . implode(', ', $schema_ur) . "\n";

$schema_up = \Illuminate\Support\Facades\Schema::getColumnListing('user_permissions');
echo "USER_PERMISSIONS: " . implode(', ', $schema_up) . "\n";

$schema_r = \Illuminate\Support\Facades\Schema::getColumnListing('roles');
echo "ROLES: " . implode(', ', $schema_r) . "\n";
