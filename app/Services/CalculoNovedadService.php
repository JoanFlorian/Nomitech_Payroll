<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Models\Salario;

class CalculoNovedadService
{
    public const OPERACION_DEVENGADO = 'devengado';
    public const OPERACION_DESCUENTO = 'deduccion';
    public const OPERACION_SIN_MOVIMIENTO = 'sin_movimiento';

    private const TIPOS_PILA = [
        'TDE',
        'TAE',
        'TDP',
        'TAP',
        'VSP',
        'VST',
        'SLN',
        'IGE',
        'IRL',
        'LMAT',
        'LPAT',
        'VAC',
        'VCT',
        'INC',
        'LIC',
    ];

    public function obtenerSalarioEmpleado(string $empleadoId): ?Salario
    {
        $empresaId = (int) session('empresa_id');
        $activePeriod = PeriodoLiquidacion::getActivePeriod();
        
        if (!$activePeriod) {
            return null;
        }

        return Salario::query()
            ->join('contrato', 'contrato.id_contrato', '=', 'salario.id_contrato')
            ->where('contrato.doc', $empleadoId)
            ->where('contrato.id_empresa', $empresaId)
            ->where('salario.id_periodo', $activePeriod->id_periodo)
            ->select('salario.*', 'contrato.salario_base as contrato_salario_base')
            ->first();
    }

    public function resolverDias(array $data): float
    {
        return max(0, (float) ($data['dias'] ?? 0));
    }

    public function resolverHoras(array $data): float
    {
        return max(0, (float) ($data['horas'] ?? 0));
    }

    public function resolverSalarioBase(?Salario $salario): float
    {
        if (!$salario) {
            return 0.0;
        }

        return (float) ($salario->contrato_salario_base ?? optional($salario->contrato)->salario_base ?? 0);
    }

    public function calcularNovedad(array $data): array
    {
        $tipo = $this->normalizarTipoNovedad((string) ($data['tipo_novedad'] ?? ''));
        $salarioBase = max(0, (float) ($data['salario_base'] ?? 0));
        $dias = $this->resolverDias($data);
        $horas = $this->resolverHoras($data);
        $valorManual = (float) ($data['valor_manual'] ?? ($data['pago_manual'] ?? 0));
        $tipoLicencia = strtolower(trim((string) ($data['tipo_licencia'] ?? '')));
        $tipoIncapacidad = strtolower(trim((string) ($data['tipo_incapacidad'] ?? '')));
        $esRemunerada = filter_var($data['es_remunerado'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $valorDia = $salarioBase > 0 ? ($salarioBase / 30) : 0;
        $valorHora = $salarioBase > 0 ? ($salarioBase / 240) : 0;

        $resultado = [
            'valor_calculado' => 0.0,
            'tipo_movimiento' => self::OPERACION_SIN_MOVIMIENTO,
            'afecta_ibc' => false,
            'tipo_novedad' => $tipo,
            'valor_dia' => $valorDia,
            'valor_hora' => $valorHora,
            'descuento_salario' => 0.0,
        ];

        switch ($tipo) {
            case 'TDE':
            case 'TAE':
            case 'TDP':
            case 'TAP':
            case 'VSP':
                $resultado['valor_calculado'] = max(0, $valorManual);
                return $resultado;

            case 'VCT':
                return $resultado;

            case 'VST':
                $resultado['valor_calculado'] = max(0, $salarioBase + max(0, $valorManual));
                $resultado['tipo_movimiento'] = self::OPERACION_DEVENGADO;
                $resultado['afecta_ibc'] = true;
                return $resultado;

            case 'SLN':
                // SLN no genera deducción monetaria; reduce los días trabajados en nómina.
                $resultado['valor_calculado'] = 0.0;
                $resultado['tipo_movimiento'] = self::OPERACION_SIN_MOVIMIENTO;
                $resultado['afecta_ibc'] = true;
                $resultado['dias_suspension'] = $dias;
                return $resultado;

            case 'IGE':
                // Normativa colombiana: solo los primeros 2 días son responsabilidad del
                // empleador (al 66.67%). Los días restantes los paga la EPS directamente.
                $diasPagados = min(2.0, max(0.0, $dias));
                // En modo horas: 2 días = 16 horas máximo pagadas por el empleador.
                $horasPagadas = ($dias > 0 || $horas <= 0) ? 0.0 : min(16.0, max(0.0, $horas));
                $resultado['valor_calculado'] = ($valorDia * $diasPagados * 0.6667) + ($valorHora * $horasPagadas * 0.6667);
                $resultado['tipo_movimiento'] = self::OPERACION_DEVENGADO;
                $resultado['afecta_ibc'] = true;
                $resultado['descuento_salario'] = ($valorDia * $dias) + ($valorHora * $horas);
                $resultado['dias_pagados_empleador'] = $diasPagados;
                return $resultado;

            case 'IRL':
                $resultado['valor_calculado'] = ($valorDia * $dias) + ($valorHora * $horas);
                $resultado['tipo_movimiento'] = self::OPERACION_DEVENGADO;
                $resultado['afecta_ibc'] = true;
                return $resultado;

            case 'LMAT':
            case 'LPAT':
            case 'VAC':
                $resultado['valor_calculado'] = $valorDia * $dias;
                $resultado['tipo_movimiento'] = self::OPERACION_DEVENGADO;
                $resultado['afecta_ibc'] = true;
                return $resultado;

            case 'INC':
                if (in_array($tipoIncapacidad, ['irl', 'riesgo_laboral'], true)) {
                    // INC tipo ARL: 100% de los días registrados (igual que IRL).
                    $resultado['valor_calculado'] = ($valorDia * $dias) + ($valorHora * $horas);
                } else {
                    // INC tipo EG: mismo tratamiento que IGE — solo 2 días al 66.67%.
                    $diasPagadosInc = min(2.0, max(0.0, $dias));
                    $horasPagadasInc = ($dias > 0 || $horas <= 0) ? 0.0 : min(16.0, max(0.0, $horas));
                    $resultado['valor_calculado'] = ($valorDia * $diasPagadosInc * 0.6667) + ($valorHora * $horasPagadasInc * 0.6667);
                    $resultado['descuento_salario'] = ($valorDia * $dias) + ($valorHora * $horas);
                    $resultado['dias_pagados_empleador'] = $diasPagadosInc;
                }
                $resultado['tipo_movimiento'] = self::OPERACION_DEVENGADO;
                $resultado['afecta_ibc'] = true;
                return $resultado;

            case 'LIC':
                $esNoRemunerada = in_array($tipoLicencia, ['no_remunerada', 'no remunerada'], true) || !$esRemunerada;
                $resultado['valor_calculado'] = ($valorDia * $dias) + ($valorHora * $horas);
                $resultado['tipo_movimiento'] = $esNoRemunerada ? self::OPERACION_DESCUENTO : self::OPERACION_DEVENGADO;
                $resultado['afecta_ibc'] = true;
                return $resultado;
        }

        return $resultado;
    }

    public function calcularValor(array $data, float $salarioBase): float
    {
        $resultado = $this->calcularNovedad(array_merge($data, ['salario_base' => $salarioBase]));
        $valor = (float) ($resultado['valor_calculado'] ?? 0);

        if (($resultado['tipo_movimiento'] ?? self::OPERACION_SIN_MOVIMIENTO) === self::OPERACION_DESCUENTO) {
            return -$valor;
        }

        return $valor;
    }

    public function esNovedadAutomatica(string $tipoNovedad): bool
    {
        $tipo = $this->normalizarTipoNovedad($tipoNovedad);

        return !in_array($tipo, ['VSP', 'VST'], true);
    }

    public function calcularValorNovedad(float $salarioBase, string $tipoNovedad, float $cantidad, string $unidad, ?string $operacionOverride = null): float
    {
        $data = [
            'tipo_novedad' => $tipoNovedad,
            'salario_base' => $salarioBase,
            'dias' => $unidad === 'dias' ? $cantidad : 0,
            'horas' => $unidad === 'horas' ? $cantidad : 0,
        ];

        $resultado = $this->calcularNovedad($data);
        $valor = (float) ($resultado['valor_calculado'] ?? 0);
        $operacion = $operacionOverride ?? (string) ($resultado['tipo_movimiento'] ?? self::OPERACION_SIN_MOVIMIENTO);

        return $operacion === self::OPERACION_DESCUENTO ? -$valor : $valor;
    }

    public function resolverOperacion(string $tipoNovedad): string
    {
        $resultado = $this->calcularNovedad(['tipo_novedad' => $tipoNovedad]);

        return (string) ($resultado['tipo_movimiento'] ?? self::OPERACION_SIN_MOVIMIENTO);
    }

    public function resolverOperacionConContexto(string $tipoNovedad, array $data): string
    {
        $resultado = $this->calcularNovedad(array_merge($data, ['tipo_novedad' => $tipoNovedad]));

        return (string) ($resultado['tipo_movimiento'] ?? self::OPERACION_SIN_MOVIMIENTO);
    }

    private function normalizarTipoNovedad(string $tipoNovedad): string
    {
        $normalized = strtolower(trim($tipoNovedad));
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $normalized);
        $normalized = str_replace(['-', ' '], '_', $normalized);

        $codigo = strtoupper($normalized);
        if (in_array($codigo, self::TIPOS_PILA, true)) {
            return $codigo;
        }

        return match ($normalized) {
            'incapacidad', 'incapacidad_enfermedad', 'incapacidad_enfermedad_general' => 'IGE',
            'incapacidad_laboral', 'incapacidad_laboral_arl' => 'IRL',
            'licencia_maternidad', 'licencia_de_maternidad' => 'LMAT',
            'licencia_paternidad', 'licencia_de_paternidad' => 'LPAT',
            'licencia_por_luto', 'licencia_luto' => 'LIC',
            'licencia_remunerada', 'licencia_no_remunerada', 'licencia' => 'LIC',
            'calamidad_domestica', 'permiso_remunerado', 'permiso_no_remunerado', 'permiso', 'cita_medica' => 'LIC',
            'ausencia_injustificada', 'suspension', 'suspension_contrato' => 'SLN',
            default => strtoupper($normalized),
        };
    }
}
