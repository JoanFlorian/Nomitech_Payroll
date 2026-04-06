<?php

namespace App\Services\Payroll;

use App\Models\Novedad;
use App\Models\PeriodoLiquidacion;
use App\Models\TipoNovedad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransitoriaSalarioDetectionService
{
    private const CODIGO = 'VST';
    private const NOMBRE = 'VST - Variación transitoria de salario';

    /**
     * Detecta y registra automáticamente novedades de Variación Transitoria de Salario (VST)
     * para empleados que tuvieron conceptos variables durante el periodo cerrado.
     *
     * @param PeriodoLiquidacion $periodo
     * @return int Cantidad de registros VST creados
     */
    public function detectarYRegistrarVST(PeriodoLiquidacion $periodo): int
    {
        Log::info("TransitoriaSalarioDetectionService: Iniciando detección VST para Periodo ID: {$periodo->id_periodo}");

        $empleadosConVST = $this->obtenerEmpleadosConConceptosVariables($periodo);

        if ($empleadosConVST->isEmpty()) {
            Log::info("TransitoriaSalarioDetectionService: No se encontraron empleados con conceptos variables en el periodo {$periodo->id_periodo}");
            return 0;
        }

        $tipoNovedadId = TipoNovedad::firstOrCreate(['nombre' => self::NOMBRE])->id_tipo_novedad;

        $registrosCreados = 0;

        foreach ($empleadosConVST as $empleado) {
            // Evitar duplicados: un VST por empleado por periodo
            $existeVST = Novedad::where('empleado_id', $empleado->empleado_id)
                ->where('tipo_novedad_codigo', self::CODIGO)
                ->where('id_periodo', $periodo->id_periodo)
                ->exists();

            if ($existeVST) {
                Log::info("TransitoriaSalarioDetectionService: VST ya registrado para empleado {$empleado->empleado_id} en periodo {$periodo->id_periodo}");
                continue;
            }

            $this->registrarNovedadVST($periodo, $empleado, $tipoNovedadId);
            $registrosCreados++;
        }

        Log::info("TransitoriaSalarioDetectionService: Se registraron {$registrosCreados} novedades VST para el periodo {$periodo->id_periodo}");

        return $registrosCreados;
    }

    /**
     * Obtiene los empleados que tuvieron conceptos variables de salario durante el periodo.
     *
     * Conceptos variables considerados:
     * - Horas extras (horas_extra > 0 o valor_horas_extras_recargos > 0)
     * - Bonificaciones (bonificaciones > 0)
     * - Comisiones (comisiones > 0)
     * - Otros devengos (otros_devengos > 0)
     *
     * @param PeriodoLiquidacion $periodo
     * @return \Illuminate\Support\Collection
     */
    private function obtenerEmpleadosConConceptosVariables(PeriodoLiquidacion $periodo)
    {
        return DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->join('usuario as u', 'u.doc', '=', 'c.doc')
            ->where('s.id_periodo', $periodo->id_periodo)
            ->where(function ($query) {
                $query->where('s.horas_extra', '>', 0)
                    ->orWhere('s.valor_horas_extras_recargos', '>', 0)
                    ->orWhere('s.bonificaciones', '>', 0)
                    ->orWhere('s.comisiones', '>', 0)
                    ->orWhere('s.otros_devengos', '>', 0);
            })
            ->select([
                'c.doc as empleado_id',
                'u.primer_nombre as empleado_nombre',
                'u.primer_apellido as empleado_apellido',
                's.id_salario',
                DB::raw('COALESCE(s.horas_extra, 0) + COALESCE(s.valor_horas_extras_recargos, 0) + COALESCE(s.bonificaciones, 0) + COALESCE(s.comisiones, 0) + COALESCE(s.otros_devengos, 0) as total_conceptos_variables'),
            ])
            ->get();
    }

    /**
     * Registra la novedad VST en la tabla novedad (visible en historial de novedades).
     *
     * @param PeriodoLiquidacion $periodo
     * @param object $empleado
     * @param int $tipoNovedadId
     * @return void
     */
    private function registrarNovedadVST(PeriodoLiquidacion $periodo, object $empleado, int $tipoNovedadId): void
    {
        $total = (float) $empleado->total_conceptos_variables;

        $observaciones = sprintf(
            'Variación Transitoria de Salario detectada automáticamente al cerrar el periodo %s - %s. Total conceptos variables: $%s',
            $periodo->fecha_inicio->format('d/m/Y'),
            $periodo->fecha_fin->format('d/m/Y'),
            number_format($total, 2, ',', '.')
        );

        Novedad::create([
            'id_tipo_novedad'    => $tipoNovedadId,
            'id_salario'         => $empleado->id_salario,
            'id_periodo'         => $periodo->id_periodo,
            'empleado_id'        => $empleado->empleado_id,
            'estado'             => Novedad::ESTADO_CERRADA,
            'tipo_novedad_nombre'=> self::NOMBRE,
            'tipo_novedad_codigo'=> self::CODIGO,
            'fecha'              => $periodo->fecha_inicio,
            'fecha_inicio'       => $periodo->fecha_inicio,
            'fecha_fin'          => $periodo->fecha_fin,
            'unidad_cantidad'    => 'dias',
            'dias'               => 0,
            'horas'              => 0,
            'cantidad'           => 0,
            'valor_calculado'    => $total,
            'valor_novedad'      => $total,
            'pago'               => $total,
            'tipo_movimiento'    => 'sin_movimiento',
            'afecta_nomina'      => false,
            'afecta_ibc'         => false,
            'es_remunerado'      => false,
            'observaciones'      => $observaciones,
            'periodo_aplicado_id'=> $periodo->id_periodo,
        ]);

        Log::info("TransitoriaSalarioDetectionService: Registrada novedad VST para empleado {$empleado->empleado_id} ({$empleado->empleado_nombre} {$empleado->empleado_apellido}) con valor \${$total}");
    }
}
