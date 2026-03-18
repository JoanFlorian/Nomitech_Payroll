<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$roles = DB::table('rol')->get();
foreach ($roles as $role) {
    echo $role->id_rol . '|' . $role->nombre . PHP_EOL;
}
