<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Permission::where('name', 'close_period')->first();
if ($p) {
    echo "ID:" . $p->id . "\n";
} else {
    echo "NOT_FOUND\n";
}
