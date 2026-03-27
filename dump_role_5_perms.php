<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rol = App\Models\Rol::where('id_rol', 5)->with('permissions')->first();
$output = "";
foreach ($rol->permissions as $p) {
    $output .= "NAME: {$p->name} | MOD: {$p->module}\n";
}
file_put_contents('role_5_perms_dump.txt', $output);
echo "Done\n";
