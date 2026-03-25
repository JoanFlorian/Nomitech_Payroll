<?php

namespace App\Services\Payroll;

use App\Models\Novedad;
use App\Models\PeriodoLiquidacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para manejar el rollover de licencias de maternidad/paternidad
 * cuando se cierra un período.
 * 
 * Cuando una licencia de maternidad se extiende más allá del período actual:
 * 1. Calcula cuántos días se usaron EN ESTE período
 * 2. Resta esos días del total de la licencia
 * 3. Almacena en `dias_restantes_rollover` para continuar en siguiente período
 * 
 * El procesamiento del rollover se hace al crear el salario en el próximo período.
 */
class MaternityLicenseRolloverService
{
    /**
     * Procesar novedades de maternidad/paternidad que continúan en próximos períodos.
     * Se ejecuta antes de cerrar las novedades del período.
     */
    public function processMaternityRollover(PeriodoLiquidacion $periodo): void
    {
        $fechaInicioPeriodo = Carbon::parse($periodo->fecha_inicio);
        $fechaFinPeriodo = Carbon::parse($periodo->fecha_fin);

        // Buscar todas las novedades de maternidad/paternidad en ESTE período
        $licencias = DB::table('novedad as n')
            ->join('salario as s', 's.id_salario', '=', 'n.id_salario')
            ->where('s.id_periodo', $periodo->id_periodo)
            ->whereIn('n.tipo_novedad_codigo', ['LMAT', 'LPAT'])
            ->where('n.estado', Novedad::ESTADO_ACTIVA)
            ->select([
                'n.id_novedad',
                'n.dias',
                'n.fecha_inicio',
                'n.fecha_fin',
                's.id_contrato',
                's.id_salario',
            ])
            ->get();

        foreach ($licencias as $licencia) {
            // Calcular fecha de fin usando campo 'dias' si fecha_fin no está explícita
            $fechaInicio = Carbon::parse($licencia->fecha_inicio);
            
            if ($licencia->fecha_fin) {
                $fechaFin = Carbon::parse($licencia->fecha_fin);
            } else {
                // Si no hay fecha_fin pero hay días, calcular
                $diasTotal = (int) ($licencia->dias ?? 0);
                $fechaFin = $fechaInicio->copy()->addDays($diasTotal - 1);
            }

            // Si la licencia se extiende más allá del período actual
            if ($fechaFin->greaterThan($fechaFinPeriodo)) {
                \Illuminate\Support\Facades\Log::info("ROLLOVER: Licencia {$licencia->id_novedad} se extiende más allá del período. Procesando...");

                // 1. Calcular cuántos días se usaron EN ESTE PERÍODO
                $diasEnEstePeriodo = $this->calcularDiasEnPeriodo(
                    $fechaInicio,
                    $fechaFin,
                    $fechaInicioPeriodo,
                    $fechaFinPeriodo
                );

                // 2. Calcular días restantes
                $diasTotales = (int) ($licencia->dias ?? 0);
                if ($diasTotales === 0) {
                    // Si no está en el campo dias, calcularlo desde fechas
                    $diasTotales = $fechaInicio->diffInDays($fechaFin) + 1;
                }
                $diasRestantes = $diasTotales - $diasEnEstePeriodo;

                \Illuminate\Support\Facades\Log::info("ROLLOVER: Días totales: {$diasTotales}, Usados: {$diasEnEstePeriodo}, Restantes: {$diasRestantes}");

                // 3. Actualizar la novedad actual con fecha_fin truncada
                Novedad::where('id_novedad', $licencia->id_novedad)
                    ->update([
                        'fecha_fin' => $fechaFinPeriodo->toDateString(),
                        'dias_restantes_rollover' => max(0, $diasRestantes),
                    ]);

                \Illuminate\Support\Facades\Log::info("ROLLOVER: Novedad {$licencia->id_novedad} actualizada con dias_restantes_rollover = {$diasRestantes}");
            }
        }
    }

    /**
     * Calcular cuántos días de una licencia caen dentro de un período
     */
    private function calcularDiasEnPeriodo(
        Carbon $licenciaInicio,
        Carbon $licenciaFin,
        Carbon $periodoInicio,
        Carbon $periodoFin
    ): int {
        $efectivoInicio = $licenciaInicio->greaterThan($periodoInicio) ? $licenciaInicio : $periodoInicio;
        $efectivoFin = $licenciaFin->lessThan($periodoFin) ? $licenciaFin : $periodoFin;

        if ($efectivoInicio->greaterThan($efectivoFin)) {
            return 0;
        }

        return $efectivoInicio->diffInDays($efectivoFin) + 1;
    }
}
