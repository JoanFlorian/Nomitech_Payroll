<?php

use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Services\ContractTerminationService;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = $app->make(ContractTerminationService::class);

echo "Testing ContractTerminationService...\n";

// Mock a contract and period
$periodo = new PeriodoLiquidacion();
$periodo->fecha_inicio = '2026-03-01';
$periodo->fecha_fin = '2026-03-31';

$contrato = new Contrato();
$contrato->fecha_fin = '2026-03-15';
$contrato->doc = 'TEST12345';

echo "Calculating final days for end date 2026-03-15: ";
$days = $service->calculateFinalDays($contrato, $periodo);
echo $days . " (Expected: 15)\n";

$contrato->fecha_fin = '2026-03-31';
echo "Calculating final days for end date 2026-03-31: ";
$days = $service->calculateFinalDays($contrato, $periodo);
echo $days . " (Expected: 30)\n";

$contrato->fecha_fin = '2026-03-30';
echo "Calculating final days for end date 2026-03-30: ";
$days = $service->calculateFinalDays($contrato, $periodo);
echo $days . " (Expected: 30)\n";

echo "Done.\n";
