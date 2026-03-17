<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Permission;

$translations = [
    'employees' => 'Empleados',
    'payroll' => 'Nómina',
    'periods' => 'Periodos',
    'provisions' => 'Provisiones',
    'reports' => 'Reportes',
    'electronic_payroll' => 'Nómina Electrónica',
    'pila' => 'PILA',
    'catalogos' => 'Catálogos'
];

foreach ($translations as $old => $new) {
    Permission::where('module', $old)->update(['module' => $new]);
    echo "Updated $old to $new\n";
}

// Case-insensitive check for common ones that might be lowercase
$lowercaseOnes = ['novedades', 'empleados', 'nomina', 'periodos', 'provisiones', 'reportes'];
foreach ($lowercaseOnes as $low) {
    Permission::whereRaw('LOWER(module) = ?', [$low])->update(['module' => ucfirst($low)]);
}
echo "Database updated.\n";
