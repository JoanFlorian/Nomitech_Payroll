<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$id10 = 10;
$id29 = 29;

$roles10 = DB::table('role_permissions')->where('permission_id', $id10)->pluck('role_id')->toArray();
$users10 = DB::table('user_permissions')->where('permission_id', $id10)->pluck('user_id')->toArray();

$roles29 = DB::table('role_permissions')->where('permission_id', $id29)->pluck('role_id')->toArray();
$users29 = DB::table('user_permissions')->where('permission_id', $id29)->pluck('user_id')->toArray();

$output = "PERMISSION 10 (close_payroll) USAGE:\n";
$output .= "Roles: " . implode(',', $roles10) . " (Count: " . count($roles10) . ")\n";
$output .= "Users: " . implode(',', $users10) . " (Count: " . count($users10) . ")\n";

$output .= "\nPERMISSION 29 (close_period) USAGE:\n";
$output .= "Roles: " . implode(',', $roles29) . " (Count: " . count($roles29) . ")\n";
$output .= "Users: " . implode(',', $users29) . " (Count: " . count($users29) . ")\n";

file_put_contents('usage_counts.txt', $output);
echo "DONE\n";
