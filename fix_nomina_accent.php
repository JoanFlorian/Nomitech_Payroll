<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$affected = DB::table('permissions')->where('module', 'Nómina')->update(['module' => 'Nomina']);
echo "SUCCESS: Updated $affected permissions to match 'Nomina'.\n";

// Clear cache as well
\Illuminate\Support\Facades\Cache::flush();
echo "CACHE_FLUSHED\n";
