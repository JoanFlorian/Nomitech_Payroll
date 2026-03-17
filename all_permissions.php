<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ps = \App\Models\Permission::all();
foreach($ps as $p) {
    echo "ID: {$p->id} | NAME: {$p->name} | MOD: {$p->module}\n";
}
