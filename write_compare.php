<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p10 = \App\Models\Permission::find(10);
$p29 = \App\Models\Permission::find(29);

$output = "--- PERMISSION 10 ---\n" . print_r($p10 ? $p10->toArray() : 'NOT FOUND', true);
$output .= "\n--- PERMISSION 29 ---\n" . print_r($p29 ? $p29->toArray() : 'NOT FOUND', true);

file_put_contents('compare_perms_full.txt', $output);
echo "DONE\n";
