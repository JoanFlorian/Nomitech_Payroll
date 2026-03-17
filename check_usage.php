<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$id_old = 10; // close_payroll
$id_new = 29; // close_period

$roles = DB::table('role_permissions')->where('permission_id', $id_old)->count();
$users = DB::table('user_permissions')->where('permission_id', $id_old)->count();

echo "PERMISSION 10 (close_payroll) USAGE:\n";
echo "Roles: $roles\n";
echo "Users: $users\n";

$roles29 = DB::table('role_permissions')->where('permission_id', $id_new)->count();
$users29 = DB::table('user_permissions')->where('permission_id', $id_new)->count();

echo "\nPERMISSION 29 (close_period) USAGE:\n";
echo "Roles: $roles29\n";
echo "Users: $users29\n";
