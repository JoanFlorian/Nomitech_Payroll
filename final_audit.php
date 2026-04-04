<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$idContrato = 269;
$idPeriodo = 24;
$contrato = \App\Models\Contrato::find($idContrato);
$periodo = \App\Models\PeriodoLiquidacion::find($idPeriodo);

echo "--- RESETTING AND TRIGGERING TERMINATION FIX ---\n";
$termService = app(\App\Services\ContractTerminationService::class);
$termService->handleContractTermination($contrato, $periodo);

$salario = \App\Models\Salario::where('id_contrato', $idContrato)->where('id_periodo', $idPeriodo)->first();

echo "\n--- VERIFICATION FOR ANDRES ---\n";
echo "BASE DAYS (a trabajar): " . (int)$salario->dias_a_trabajar . "\n";
echo "ABSENCE DAYS (novedades): " . (int)$salario->dias_ausencia . "\n";
echo "EFFECTIVE WORK DAYS (sueldo): " . (int)$salario->dias_trabajados . "\n";
echo "GROSS DEVENGOS: " . number_format($salario->total_devengos, 2) . "\n";
echo "NET PAY: " . number_format($salario->salario_neto, 2) . "\n";
