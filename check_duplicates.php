<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$perms = \App\Models\Permission::where('name', 'close_period')->get();
echo "COUNT: " . $perms->count() . "\n";
foreach($perms as $p) {
    echo "ID: " . $p->id . " NAME: " . $p->name . " MODULE: " . $p->module . "\n";
}
