<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ps = \App\Models\Permission::all();
$modules = [];
foreach($ps as $p) {
    if (!isset($modules[$p->module])) {
        $modules[$p->module] = [];
    }
    $modules[$p->module][] = $p->name;
}

foreach($modules as $mod => $names) {
    echo "MOD: [$mod] | PERMS: " . implode(', ', $names) . "\n";
}
