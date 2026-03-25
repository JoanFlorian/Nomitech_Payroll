<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('pila_archivos')) {
    DB::table('pila_archivos')->delete();
    echo "✓ Tablapila_archivos limpiada\n";
}

if (Schema::hasTable('planilla_pila')) {
    DB::table('planilla_pila')->delete();
    echo "✓ Tabla planilla_pila limpiada\n";
}

echo "\n✓ Base de datos de PILA limpiada correctamente\n";
