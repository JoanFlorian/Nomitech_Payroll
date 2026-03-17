<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ps = \App\Models\Permission::all();
$output = "";
foreach($ps as $p) {
    $output .= "ID: {$p->id} | NAME: {$p->name} | MOD: {$p->module}\n";
}
file_put_contents('all_perms_dump.txt', $output);
echo "DONE\n";
