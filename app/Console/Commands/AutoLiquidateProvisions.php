<?php

namespace App\Console\Commands;

use App\Models\ProvisionAutomation;
use App\Models\PeriodoLiquidacion;
use App\Services\Benefits\BenefitPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\BenefitLedger;

class AutoLiquidateProvisions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provisions:auto-liquidate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically liquidate social benefits based on scheduled dates per company.';

    /**
     * Execute the console command.
     */
    public function handle(BenefitPaymentService $service)
    {
        $today = now('America/Bogota');
        $day = $today->day;
        $month = $today->month;
        $year = $today->year;

        $this->info("Current Time (Bogota): " . $today->toDateTimeString());
        $this->info("Looking for Day: $day, Month: $month, Year: $year");

        // Find active automations for today (no yearly limit for demo flexibility)
        $automations = ProvisionAutomation::where('is_active', true)
            ->where('execution_day', $day)
            ->where('execution_month', $month)
            ->get();

        if ($automations->isEmpty()) {
            $this->info("No provisions scheduled for liquidation today ({$day}/{$month}).");
            return 0;
        }

        foreach ($automations as $auto) {
            $this->info("Processing: {$auto->benefit_type} for Empresa ID: {$auto->id_empresa}");
            
            try {
                $periodId = null;
                if ($auto->payment_mode === 'payroll') {
                    // Find active period for THIS specific company
                    $period = PeriodoLiquidacion::where('id_empresa', $auto->id_empresa)
                        ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
                        ->first();
                    
                    if (!$period) {
                        $this->warn("Skipping {$auto->benefit_type} for Empresa {$auto->id_empresa}: No open payroll period found.");
                        Log::warning("AutoLiquidateProvisions: Skipped Empresa {$auto->id_empresa} for {$auto->benefit_type} due to no open period.");
                        continue;
                    }
                    $periodId = $period->id_periodo;
                }

                // Map UI types to internal benefit types
                $benefitType = $auto->benefit_type;
                if (str_starts_with($benefitType, 'prima_')) {
                    $benefitType = 'prima';
                }

                // Trigger the mass liquidation logic
                $result = $service->liquidateMass(
                    $benefitType,
                    $auto->id_empresa,
                    $auto->payment_mode,
                    $periodId
                );

                // AUTO-CONSIGNMENT GENERATION FOR CESANTÍAS
                if ($benefitType === BenefitLedger::TYPE_CESANTIAS) {
                    $cYear = date('m') <= 2 ? date('Y') - 1 : date('Y');
                    $batchesData = $service->generarConsignacionAnual($auto->id_empresa, $cYear);
                    if (!empty($batchesData)) {
                        $zipPath = $service->generateConsignmentZip($batchesData, $cYear);
                        if ($zipPath) {
                            $savePath = "consignaciones_auto/empresa_{$auto->id_empresa}_" . now()->format('Ymd_His') . "_" . basename($zipPath);
                            Storage::disk('local')->put($savePath, file_get_contents($zipPath));
                            Log::info("AutoLiquidateProvisions: Consignación Anual generated at {$savePath}");
                        }
                    }
                }

                // Track execution (informational only, no longer blocks re-runs)
                $auto->update(['last_execution_year' => $year]);
                
                $this->info("Success: Processed {$result['count']} employees for {$auto->benefit_type}.");
                Log::info("AutoLiquidateProvisions: Success for Empresa {$auto->id_empresa}, Type: {$auto->benefit_type}, Mode: {$auto->payment_mode}");

            } catch (\Exception $e) {
                $this->error("Error processing {$auto->benefit_type} for Empresa {$auto->id_empresa}: " . $e->getMessage());
                Log::error("AutoLiquidateProvisions: Error for Empresa {$auto->id_empresa}", [
                    'type' => $auto->benefit_type,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return 0;
    }
}
