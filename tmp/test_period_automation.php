<?php

use App\Models\Empresa;
use App\Models\PeriodoLiquidacion;
use App\Services\Payroll\PeriodoAutomationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// 1. Setup Mock Empresa
$empresa = Empresa::first() ?? Empresa::factory()->create();

$service = new PeriodoAutomationService();

function testRule(PeriodoAutomationService $service, Empresa $empresa, string $testDate, string $label)
{
    echo "--- Testing: $label (Date: $testDate) ---\n";
    Carbon::setTestNow(Carbon::parse($testDate));

    // Clear previous periods for this test if any (only for clean testing)
    // DB::table('periodo_liquidacion')->where('id_empresa', $empresa->id_empresa)->delete();

    $periodo = $service->handleLicenseActivation($empresa);

    echo "Resulting Period: {$periodo->fecha_inicio->toDateString()} to {$periodo->fecha_fin->toDateString()}\n";

    $expectedStart = Carbon::parse($testDate)->day < 20
        ? Carbon::parse($testDate)->startOfMonth()
        : Carbon::parse($testDate)->addMonth()->startOfMonth();

    if ($periodo->fecha_inicio->toDateString() === $expectedStart->toDateString()) {
        echo "SUCCESS: Period starts on expected date.\n";
    } else {
        echo "FAILED: Expected start {$expectedStart->toDateString()}, got {$periodo->fecha_inicio->toDateString()}\n";
    }
    echo "\n";
}

// Test Case 1: Before Day 20 (e.g., March 5th)
testRule($service, $empresa, '2026-03-05', 'Before Day 20');

// Test Case 2: After Day 20 (e.g., March 25th)
testRule($service, $empresa, '2026-03-25', 'After Day 20');

// Test Case 3: Renewal with Closed Period
echo "--- Testing: Renewal with Closed Period ---\n";
$lastPeriod = PeriodoLiquidacion::where('id_empresa', $empresa->id_empresa)->orderByDesc('fecha_inicio')->first();
$lastPeriod->update(['estado' => PeriodoLiquidacion::ESTADO_CERRADO]);

Carbon::setTestNow(Carbon::parse('2026-04-05'));
$newPeriod = $service->handleLicenseActivation($empresa);
echo "New Period after renewal: {$newPeriod->fecha_inicio->toDateString()}\n";

// Reset TestNow
Carbon::setTestNow();
