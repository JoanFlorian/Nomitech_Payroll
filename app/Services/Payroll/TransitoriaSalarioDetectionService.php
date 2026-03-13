<?php

namespace App\Services\Payroll;

use App\Models\PeriodoLiquidacion;
use App\Models\HistorialNovedad;
use App\Models\Salario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransitoriaSalarioDetectionService
{
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

        // Obtener todos los salarios del periodo con conceptos variables
        $empleadosConVST = $this->obtenerEmpleadosConConceptosVariables($periodo);

        if ($empleadosConVST->isEmpty()) {
            Log::info("TransitoriaSalarioDetectionService: No se encontraron empleados con conceptos variables en el periodo {$periodo->id_periodo}");
            return 0;
        }

        $registrosCreados = 0;

        foreach ($empleadosConVST as $empleado) {
            // Evitar duplicados: verificar si ya existe un registro VST para este empleado en este periodo
            $existeVST = HistorialNovedad::where('empleado_id', $empleado->empleado_id)
                ->where('tipo_novedad', 'VST - Variación Transitoria de Salario')
                ->whereBetween('created_at', [
                    $periodo->fecha_inicio,
                    now()
                ])
                ->exists();

            if ($existeVST) {
                Log::info("TransitoriaSalarioDetectionService: VST ya registrado para empleado {$empleado->empleado_id} en periodo {$periodo->id_periodo}");
                continue;
            }

            // Registrar la novedad VST en historial_novedades
            $this->registrarNovedadVST($periodo, $empleado);
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
                DB::raw("CONCAT(u.primer_nombre, ' ', u.primer_apellido) as empleado_nombre"),
                's.id_salario',
                DB::raw('COALESCE(s.horas_extra, 0) + COALESCE(s.valor_horas_extras_recargos, 0) + COALESCE(s.bonificaciones, 0) + COALESCE(s.comisiones, 0) + COALESCE(s.otros_devengos, 0) as total_conceptos_variables')
            ])
            ->get();
    }

    /**
     * Registra la novedad de Variación Transitoria de Salario en historial_novedades.
     *
     * @param PeriodoLiquidacion $periodo
     * @param object $empleado
     * @return void
     */
    private function registrarNovedadVST(PeriodoLiquidacion $periodo, object $empleado): void
    {
        $observaciones = sprintf(
            'Variación Transitoria de Salario detectada automáticamente al cerrar el periodo %s - %s. Total conceptos variables: $%s',
            $periodo->fecha_inicio->format('d/m/Y'),
            $periodo->fecha_fin->format('d/m/Y'),
            number_format($empleado->total_conceptos_variables, 2, ',', '.')
        );

        $usuario = auth()->user();

        HistorialNovedad::create([
            'id_novedad' => null, // No proviene de una novedad manual
            'id_salario' => $empleado->id_salario,
            'empleado_id' => $empleado->empleado_id,
            'tipo_novedad' => 'VST - Variación Transitoria de Salario',
            'fecha_inicio' => $periodo->fecha_inicio,
            'fecha_fin' => $periodo->fecha_fin,
            'valor' => $empleado->total_conceptos_variables,
            'observaciones' => $observaciones,
            'accion' => 'crear',
            'id_usuario' => $usuario ? $usuario->doc : null,
            'usuario_nombre' => $usuario ? trim(($usuario->primer_nombre ?? '') . ' ' . ($usuario->primer_apellido ?? '')) : 'Sistema Automático',
        ]);

        Log::info("TransitoriaSalarioDetectionService: Registrada novedad VST para empleado {$empleado->empleado_id} ({$empleado->empleado_nombre}) con valor \${$empleado->total_conceptos_variables}");
    }
}
