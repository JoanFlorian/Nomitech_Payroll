<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Permission::where('name', 'close_period')->first();
if ($p) {
    echo "FOUND: " . $p->name . " | MODULE: " . $p->module . " | DESC: " . $p->description . "\n";
} else {
    echo "NOT_FOUND: close_period\n";
}

$p2 = \App\Models\Permission::where('name', 'close_payroll')->first();
if ($p2) {
    echo "STILL_EXISTS: close_payroll (Should have been deleted)\n";
} else {
    echo "CONFIRMED: close_payroll deleted.\n";
}
