<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM user_permissions");
foreach($indexes as $idx) {
    echo "TABLE: {$idx->Table} NON_UNIQUE: {$idx->Non_unique} KEY_NAME: {$idx->Key_name} COLUMN: {$idx->Column_name}\n";
}
