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
    $modules[$p->module][] = $p->name . " (ID:" . $p->id . ")";
}

$output = "";
foreach($modules as $mod => $names) {
    $output .= "MOD: [$mod] | PERMS: " . implode(', ', $names) . "\n";
}
file_put_contents('mod_perms_result.txt', $output);
echo "DONE\n";
