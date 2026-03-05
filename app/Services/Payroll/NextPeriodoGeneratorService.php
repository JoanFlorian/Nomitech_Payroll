<?php

namespace App\Services\Payroll;

use App\Models\PeriodoLiquidacion;
use Carbon\Carbon;
use Exception;

class NextPeriodoGeneratorService
{
    /**
     * Genera el siguiente periodo de liquidación basado en la frecuencia del periodo actual.
     *
     * @param PeriodoLiquidacion $periodo
     * @return PeriodoLiquidacion
     * @throws Exception
     */
    public function generarSiguiente(PeriodoLiquidacion $periodo): PeriodoLiquidacion
    {
        $fechaFinActual = Carbon::parse($periodo->fecha_fin);
        $frecuencia = $periodo->tipo_frecuencia;
        $empresaId = $periodo->id_empresa;

        if (!$frecuencia) {
            throw new Exception("El periodo de origen no tiene definido un tipo de frecuencia.");
        }

        if (!$empresaId) {
            throw new Exception("El periodo de origen no está asociado a ninguna empresa.");
        }

        // Definir la fecha de inicio del siguiente periodo (día después del fin actual)
        $siguienteInicio = $fechaFinActual->copy()->addDay();

        // Calcular la fecha de fin según la frecuencia usando la lógica centralizada del modelo
        $siguienteFin = PeriodoLiquidacion::calculateEndDate($siguienteInicio, $frecuencia);

        if (!$siguienteFin) {
            // Si es 'otro', no podemos autogenerar fin, lanzamos excepción o usamos un default razonable
            if ($frecuencia === PeriodoLiquidacion::FRECUENCIA_OTRO) {
                throw new Exception("No se puede autogenerar el siguiente periodo para frecuencias personalizadas (Otro).");
            }
            throw new Exception("Error al calcular la fecha fin para la frecuencia '{$frecuencia}'.");
        }

        // Verificar si ya existe un periodo para esta empresa con la misma fecha de inicio
        $existente = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->where('fecha_inicio', $siguienteInicio->toDateString())
            ->where('fecha_fin', $siguienteFin->toDateString())
            ->first();

        if ($existente) {
            return $existente;
        }

        // Crear e inyectar el nuevo periodo
        return PeriodoLiquidacion::create([
            'id_empresa' => $empresaId,
            'fecha_inicio' => $siguienteInicio->toDateString(),
            'fecha_fin' => $siguienteFin->toDateString(),
            'tipo_frecuencia' => $frecuencia,
            'estado' => PeriodoLiquidacion::ESTADO_ABIERTO, // Por defecto el siguiente se abre
        ]);
    }
}
