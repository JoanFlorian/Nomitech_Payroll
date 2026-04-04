<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$idContrato = 269;
$idPeriodo = 24;
$contrato = \App\Models\Contrato::find($idContrato);
$periodo = \App\Models\PeriodoLiquidacion::find($idPeriodo);

echo "--- RESETTING AND TRIGGERING TERMINATION FIX ---\n";
try {
    $termService = app(\App\Services\ContractTerminationService::class);
    $termService->handleContractTermination($contrato, $periodo);
    echo "SUCCESS: handleContractTermination executed.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

$salario = \App\Models\Salario::where('id_contrato', $idContrato)->where('id_periodo', $idPeriodo)->first();

echo "\n--- VERIFICATION FOR ANDRES ---\n";
if ($salario) {
    echo "SALARIO ID: " . $salario->id_salario . "\n";
    echo "BASE DAYS (a trabajar): " . $salario->dias_a_trabajar . "\n";
    echo "ABSENCE DAYS (novedades): " . $salario->dias_ausencia . "\n";
    echo "EFFECTIVE WORK DAYS (sueldo): " . $salario->dias_trabajados . "\n";
    echo "GROSS DEVENGOS: " . number_format($salario->total_devengos, 2) . "\n";
} else {
    echo "CRITICAL: No salary record found for contract 269 on period 24.\n";
}

$calc = app(\App\Services\NominaCalculatorService::class);
$r = $calc->resumirNovedadesContratoPeriodo(269, 24, 16666.67);
echo "\n--- NOVELTY SUMMARY TRACE ---\n";
echo "Dias Ausencia Total reported by service: " . $r['dias_ausencia_total'] . "\n";
