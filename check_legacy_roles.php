<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rs = DB::table('rol')->get();
foreach($rs as $r) {
    echo "ID: {$r->id_rol} NAME: {$r->nombre}\n";
}
