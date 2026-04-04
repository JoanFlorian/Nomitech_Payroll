<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$idContrato = 269;
$idPeriodo = 24;

$calc = app(\App\Services\NominaCalculatorService::class);
$input = [
    'dias_trabajados' => 15, // WE SEND 15
    'fecha_pago' => '2026-04-15'
];

echo "--- STARTING SAVE --- \n";
$salario = $calc->guardarNominaEmpleado($idContrato, $idPeriodo, $input, true);

echo "AFTER SAVE:\n";
echo "ID: " . $salario->id_salario . "\n";
echo "Base (a_trabajar): " . $salario->dias_a_trabajar . "\n";
echo "Effective (dias_trabajados): " . $salario->dias_trabajados . "\n";
echo "Absence (dias_ausencia): " . $salario->dias_ausencia . "\n";

$fresh = \App\Models\Salario::find($salario->id_salario);
echo "\nFRESH FROM DB:\n";
echo "Base (a_trabajar): " . $fresh->dias_a_trabajar . "\n";
echo "Effective (dias_trabajados): " . $fresh->dias_trabajados . "\n";
echo "Absence (dias_ausencia): " . $fresh->dias_ausencia . "\n";
