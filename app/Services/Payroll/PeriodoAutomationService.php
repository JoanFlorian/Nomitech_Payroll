<?php

namespace App\Services\Payroll;

use App\Models\Empresa;
use App\Models\PeriodoLiquidacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PeriodoAutomationService
{
    /**
     * Maneja la activación de la licencia y crea el periodo inicial o siguiente si aplica.
     */
    public function handleLicenseActivation(Empresa $empresa, ?Carbon $referenceDate = null)
    {
        Log::info("PeriodoAutomationService: Handling license activation for Empresa ID: {$empresa->id_empresa}");

        $ultimoPeriodo = PeriodoLiquidacion::where('id_empresa', $empresa->id_empresa)
            ->orderByDesc('fecha_inicio')
            ->first();

        $referenceDate = $referenceDate ?? now();

        if (!$ultimoPeriodo) {
            // Caso 1: Compra inicial (Sin periodos previos)
            Log::info("PeriodoAutomationService: Initial purchase detected.");
            return $this->createInitialPeriod($empresa, $referenceDate);
        }

        // Caso 2: Renovación (Ya existen periodos)
        if ($ultimoPeriodo->estado === PeriodoLiquidacion::ESTADO_CERRADO) {
            Log::info("PeriodoAutomationService: Renewal detected with last period closed.");
            return $this->createInitialPeriod($empresa, $referenceDate);
        }

        Log::info("PeriodoAutomationService: Activation skipped. Last period is still open.");
        return $ultimoPeriodo;
    }

    /**
     * Crea el periodo de liquidación basado en la regla del día 20.
     */
    private function createInitialPeriod(Empresa $empresa, Carbon $date)
    {
        $day = $date->day;

        if ($day < 20) {
            // Antes del 20: Mes actual
            $startDate = $date->copy()->startOfMonth();
        } else {
            // Día 20 o después: Mes siguiente
            $startDate = $date->copy()->addMonth()->startOfMonth();
        }

        $endDate = $startDate->copy()->endOfMonth();

        // Evitar duplicados
        $existente = PeriodoLiquidacion::where('id_empresa', $empresa->id_empresa)
            ->where('fecha_inicio', $startDate->toDateString())
            ->first();

        if ($existente) {
            Log::info("PeriodoAutomationService: Period already exists for {$startDate->format('Y-m')}. skipping.");
            return $existente;
        }

        Log::info("PeriodoAutomationService: Creating automatic monthly period: {$startDate->toDateString()} - {$endDate->toDateString()}");

        return PeriodoLiquidacion::create([
            'id_empresa' => $empresa->id_empresa,
            'tipo_frecuencia' => PeriodoLiquidacion::FRECUENCIA_MENSUAL,
            'fecha_inicio' => $startDate->toDateString(),
            'fecha_fin' => $endDate->toDateString(),
            'estado' => PeriodoLiquidacion::ESTADO_ABIERTO,
        ]);
    }
}
