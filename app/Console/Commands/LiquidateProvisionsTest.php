<?php

namespace App\Console\Commands;

use App\Models\BenefitLedger;
use App\Models\PeriodoLiquidacion;
use App\Services\Benefits\BenefitPaymentService;
use Illuminate\Console\Command;

class LiquidateProvisionsTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provisions:test {type : Benefit type (prima, cesantias, intereses_cesantias, vacaciones)} {company_id : The ID of the company} {mode=direct : Payment mode (direct or payroll)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually trigger a mass liquidation for testing purposes, bypassing schedule checks.';

    /**
     * Execute the console command.
     */
    public function handle(BenefitPaymentService $service)
    {
        $type = $this->argument('type');
        $companyId = $this->argument('company_id');
        $mode = $this->argument('mode');

        // Map UI types if necessary
        if ($type === 'prima_1' || $type === 'prima_2') {
            $type = 'prima';
        }

        $this->info("🧪 Iniciando liquidación de prueba: {$type} | Empresa: {$companyId} | Modo: {$mode}");

        try {
            $periodId = null;
            if ($mode === 'payroll') {
                $period = PeriodoLiquidacion::where('id_empresa', $companyId)
                    ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
                    ->first();
                if (!$period) {
                    $this->error("❌ Error: No hay un periodo de nómina abierto para la empresa {$companyId}.");
                    return 1;
                }
                $periodId = $period->id_periodo;
            }

            $result = $service->liquidateMass($type, (int)$companyId, $mode, $periodId);

            $this->info("✅ ¡Éxito! Se procesaron {$result['count']} empleados.");
            
            if (!empty($result['skipped'])) {
                $this->warn("⚠️  " . count($result['skipped']) . " empleados fueron omitidos (ver logs para detalles).");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error durante la liquidación: " . $e->getMessage());
            return 1;
        }
    }
}
