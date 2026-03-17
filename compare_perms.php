<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p10 = \App\Models\Permission::find(10);
$p29 = \App\Models\Permission::find(29);

echo "--- PERMISSION 10 ---\n";
if ($p10) {
    print_r($p10->toArray());
} else {
    echo "NOT FOUND\n";
}

echo "\n--- PERMISSION 29 ---\n";
if ($p29) {
    print_r($p29->toArray());
} else {
    echo "NOT FOUND\n";
}
