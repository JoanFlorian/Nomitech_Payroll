<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$doc = '3000000009';
$sal = 4000000;

// Step 1: Clean everything
\Illuminate\Support\Facades\DB::table('benefit_ledger')->where('employee_id', $doc)->delete();
$b = App\Models\BenefitBalance::where('employee_id', $doc)->first();
if ($b) {
    $b->update(['prima_balance' => 0, 'cesantias_balance' => 0, 'intereses_balance' => 0, 'vacaciones_balance' => 0]);
}

// Step 2: Simulate MARCH period close (30 days of accruals)
$accrualService = app(App\Services\Benefits\BenefitAccrualService::class);
$contrato = App\Models\Contrato::where('doc', $doc)->first();
$tenantId = (int) $contrato->id_empresa;

$prima30 = $accrualService->calculatePrima($sal, 30);
$ces30 = $accrualService->calculateCesantias($sal, 30);
$vac30 = $accrualService->calculateVacaciones($sal, 30); // NOW IN DAYS!

$diasAcum = 30; // Hardcode for March close
$intGlobal = $accrualService->calculateInteresesCesantias($ces30, $diasAcum);
$int30 = max(0, $intGlobal);

echo "MARCH ACCRUALS (Vac in days now!):" . PHP_EOL;
echo "  Prima: " . $prima30 . PHP_EOL;
echo "  Cesantias: " . $ces30 . PHP_EOL;
echo "  Intereses: " . $int30 . PHP_EOL;
echo "  Vacaciones (días): " . $vac30 . PHP_EOL;

$ref = 'Causación periodo 01/03/2026 – 30/03/2026';
$accrualService->createAccrualEntry($tenantId, $doc, $contrato->id_contrato, 'prima', $prima30, 23, $ref);
$accrualService->createAccrualEntry($tenantId, $doc, $contrato->id_contrato, 'cesantias', $ces30, 23, $ref);
$accrualService->createAccrualEntry($tenantId, $doc, $contrato->id_contrato, 'intereses_cesantias', $int30, 23, $ref);
$accrualService->createAccrualEntry($tenantId, $doc, $contrato->id_contrato, 'vacaciones', $vac30, 23, $ref);

// Verify format in balance visually (Days instead of Pesos)
$b->refresh();
echo PHP_EOL . "BALANCE AFTER MARCH (Provisiones - seen in UI):" . PHP_EOL;
echo "  Vacaciones: " . $b->vacaciones_balance . " días" . PHP_EOL;

// Step 3: Run termination (schedules April accruals + payment)
$termService = app(App\Services\ContractTerminationService::class);
$periodo = App\Models\PeriodoLiquidacion::find(24);
$result = $termService->handleContractTermination($contrato, $periodo);

$b->refresh();
echo PHP_EOL . "FINAL BALANCE AFTER TERMINATION:" . PHP_EOL;
echo "  Vacaciones: " . $b->vacaciones_balance . " días (should be 1.88 for 45 days)" . PHP_EOL;

echo PHP_EOL . "PAYMENTS (Should be converted to PESOS!):" . PHP_EOL;
$payments = \Illuminate\Support\Facades\DB::table('benefit_ledger')
    ->where('employee_id', $doc)
    ->where('movement_type', 'scheduled_payment')
    ->get(['benefit_type', 'amount']);
foreach ($payments as $p) {
    echo "  " . $p->benefit_type . ": " . number_format(abs($p->amount), 2) . PHP_EOL;
}
