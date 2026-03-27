<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$modules = DB::table('permissions')->distinct()->pluck('module')->toArray();
file_put_contents('modules_dump.txt', implode("\n", $modules));
echo "Done\n";
