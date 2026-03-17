<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'cajas exists: '.(Schema::hasTable('cajas_compensacion')?'YES':'NO')."\n";
echo 'contrato.id_caja exists: '.(Schema::hasColumn('contrato','id_caja')?'YES':'NO')."\n";

if (Schema::hasColumn('contrato','id_caja')) {
    $cols = DB::select("SHOW COLUMNS FROM contrato WHERE Field = 'id_caja'");
    echo "contrato.id_caja type: {$cols[0]->Type}\n";
}

if (Schema::hasTable('cajas_compensacion')) {
    $cols = DB::select("SHOW COLUMNS FROM cajas_compensacion WHERE Field = 'id_caja'");
    echo "cajas.id_caja type: {$cols[0]->Type}\n";
    
    $status = DB::select("SHOW TABLE STATUS LIKE 'cajas_compensacion'");
    echo "cajas Engine: {$status[0]->Engine}\n";
    echo "cajas Collation: {$status[0]->Collation}\n";
}

$status = DB::select("SHOW TABLE STATUS LIKE 'contrato'");
echo "contrato Engine: {$status[0]->Engine}\n";
echo "contrato Collation: {$status[0]->Collation}\n";
