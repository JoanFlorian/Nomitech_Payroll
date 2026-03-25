<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoClosePeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'periods:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra automáticamente los periodos de liquidación que han llegado a su fecha de cierre programada.';

    /**
     * Execute the console command.
     */
    public function handle(
        \App\Services\Payroll\NextPeriodoGeneratorService $generator, 
        \App\Services\Benefits\BenefitAccrualService $accrualService, 
        \App\Services\Benefits\BenefitPaymentService $paymentService,
        \App\Services\Payroll\TransitoriaSalarioDetectionService $vstDetectionService
    ) {
        $today = now('America/Bogota')->toDateString();
        
        $periodos = \App\Models\PeriodoLiquidacion::whereIn('estado', [
                \App\Models\PeriodoLiquidacion::ESTADO_ABIERTO, 
                \App\Models\PeriodoLiquidacion::ESTADO_PENDIENTE
            ])
            ->whereNotNull('fecha_cierre_automatico')
            ->where('fecha_cierre_automatico', '<=', $today)
            ->get();

        if ($periodos->isEmpty()) {
            $this->info('No hay periodos programados para cierre automático hoy.');
            return 0;
        }

        $this->info("Procesando {$periodos->count()} periodos para cierre automático...");

        foreach ($periodos as $periodo) {
            $this->info("Cerrando periodo ID: {$periodo->id_periodo} (Empresa: {$periodo->id_empresa})...");

            try {
                // El proceso de cierre requiere que existan salarios liquidados.
                // Si no hay ninguno, el cierre manual en el controlador lanza excepción.
                // Aquí seguiremos la misma lógica.
                if ($periodo->salarios()->count() === 0) {
                    $this->warn("El periodo {$periodo->id_periodo} no tiene liquidaciones. Saltando...");
                    continue;
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($periodo, $generator, $accrualService, $paymentService, $vstDetectionService) {
                    // Actualizar todos los salarios del periodo a estado 'pagado'
                    $periodo->salarios()->update([
                        'estado' => \App\Models\Salario::ESTADO_PAGADO,
                        'updated_at' => now()
                    ]);

                    $periodo->close();

                    // Cerrar novedades activas del periodo
                    \App\Models\Novedad::where('id_periodo', $periodo->id_periodo)
                        ->where('estado', \App\Models\Novedad::ESTADO_ACTIVA)
                        ->where(function($q) use ($periodo) {
                            $q->whereNull('fecha_fin')
                              ->orWhere('fecha_fin', '<=', $periodo->fecha_fin->toDateString());
                        })
                        ->update(['estado' => \App\Models\Novedad::ESTADO_CERRADA, 'updated_at' => now()]);

                    // Detectar y registrar automáticamente VST
                    $vstDetectionService->detectarYRegistrarVST($periodo);

                    // Provisiones
                    $accrualService->generateAccrualsForPeriod($periodo);

                    // Pagos programados
                    $paymentService->processScheduledPayments($periodo);

                    // Auto-generación del siguiente periodo (por defecto habilitado para auto-cierre)
                    $generator->generarSiguiente($periodo);
                });

                $this->info("Periodo {$periodo->id_periodo} cerrado exitosamente.");

            } catch (\Exception $e) {
                $this->error("Error al cerrar periodo {$periodo->id_periodo}: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Error en AutoClosePeriods para ID {$periodo->id_periodo}: " . $e->getMessage());
            }
        }

        $this->info('Proceso de cierre automático finalizado.');
        return 0;
    }
}
