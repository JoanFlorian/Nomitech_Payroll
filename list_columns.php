<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('novedad');
foreach ($columns as $col) {
    echo $col . "\n";
}
