<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$total = DB::table('departamento')->count();
echo "Total departamentos en BD: " . $total . "\n";

// Obtener listado
$deps = DB::table('departamento')->select('codigo', 'nombre')->orderBy('codigo')->get();

echo "\nDepartamentos en BD:\n";
foreach ($deps as $dep) {
    echo "  {$dep->codigo} - {$dep->nombre}\n";
}

echo "\n\nDepartamentos esperados en seeder: 33\n";
