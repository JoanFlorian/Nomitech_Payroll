<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Salario;

class CalculoNovedadService
{
    public const OPERACION_DEVENGADO = 'devengado';
    public const OPERACION_DESCUENTO = 'descuento';

    private const NOVEDADES_AUTOMATICAS = [
        'incapacidad_enfermedad_general',
        'incapacidad_laboral_arl',
        'licencia_maternidad',
        'licencia_paternidad',
        'licencia_luto',
        'licencia_remunerada',
        'licencia_no_remunerada',
        'calamidad_domestica',
        'cita_medica',
        'permiso_remunerado',
        'permiso_no_remunerado',
        'ausencia_injustificada',
        'suspension_contrato',
    ];

    public function obtenerSalarioEmpleado(string $empleadoId): ?Salario
    {
        $empresaId = (int) session('empresa_id');

        return Salario::query()
            ->join('contrato', 'contrato.id_contrato', '=', 'salario.id_contrato')
            ->where('contrato.doc', $empleadoId)
            ->where(function ($query) {
                $query
                    ->where('contrato.activo', true)
                    ->orWhere('contrato.estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO);
            })
            ->when($empresaId > 0, function ($query) use ($empresaId) {
                $query->where('contrato.id_empresa', $empresaId);
            })
            ->orderByDesc('salario.id_salario')
            ->select('salario.*', 'contrato.salario_base as contrato_salario_base')
            ->first();
    }

    public function resolverDias(array $data): float
    {
        return (float) ($data['dias'] ?? 0);
    }

    public function resolverHoras(array $data): float
    {
        return (float) ($data['horas'] ?? 0);
    }

    public function calcularValor(array $data, float $salarioBase): float
    {
        $tipo = $this->normalizarTipoNovedad((string) ($data['tipo_novedad'] ?? ''));
        $esAutomatica = $this->esNovedadAutomatica($tipo);

        if (!$esAutomatica && isset($data['pago_manual']) && $data['pago_manual'] !== null && $data['pago_manual'] !== '') {
            return (float) $data['pago_manual'];
        }

        $dias = (float) ($data['dias'] ?? 0);
        $horas = (float) ($data['horas'] ?? 0);

        if ($tipo === 'licencia_maternidad') {
            $dias = 126;
            $horas = 0;
        }

        if ($tipo === 'cita_medica') {
            $dias = 0;
        }

        $cantidad = $horas > 0 ? $horas : $dias;
        $unidad = $horas > 0 ? 'horas' : 'dias';
        $operacion = $this->resolverOperacionConContexto($tipo, $data);

        return $this->calcularValorNovedad($salarioBase, $tipo, $cantidad, $unidad, $operacion);
    }

    public function esNovedadAutomatica(string $tipoNovedad): bool
    {
        $tipo = $this->normalizarTipoNovedad($tipoNovedad);

        return in_array($tipo, self::NOVEDADES_AUTOMATICAS, true);
    }

    public function calcularValorNovedad(float $salarioBase, string $tipoNovedad, float $cantidad, string $unidad, ?string $operacionOverride = null): float
    {
        if ($salarioBase <= 0 || $cantidad <= 0) {
            return 0;
        }

        $tipo = $this->normalizarTipoNovedad($tipoNovedad);
        $operacion = $operacionOverride ?? $this->resolverOperacion($tipo);
        $salarioDia = $salarioBase / 30;
        $salarioHora = $salarioDia / 8;

        $base = match ($tipo) {
            'incapacidad_enfermedad_general' => ($salarioDia * $cantidad) * 0.6667,
            'incapacidad_laboral_arl' => $salarioDia * $cantidad,
            'licencia_maternidad',
            'licencia_paternidad',
            'licencia_luto',
            'licencia_remunerada' => $salarioDia * $cantidad,
            'licencia_no_remunerada',
            'calamidad_domestica',
            'ausencia_injustificada',
            'suspension_contrato' => $salarioDia * $cantidad,
            'cita_medica' => $salarioHora * $cantidad,
            'permiso_remunerado',
            'permiso_no_remunerado' => $unidad === 'horas'
                ? ($salarioHora * $cantidad)
                : ($salarioDia * $cantidad),
            default => 0,
        };

        return $operacion === self::OPERACION_DESCUENTO ? -$base : $base;
    }

    public function resolverOperacion(string $tipoNovedad): string
    {
        $tipo = $this->normalizarTipoNovedad($tipoNovedad);

        return match ($tipo) {
            'incapacidad_enfermedad_general',
            'incapacidad_laboral_arl',
            'licencia_maternidad',
            'licencia_paternidad',
            'licencia_luto',
            'licencia_remunerada',
            'calamidad_domestica',
            'cita_medica',
            'permiso_remunerado' => self::OPERACION_DEVENGADO,
            'licencia_no_remunerada',
            'permiso_no_remunerado',
            'ausencia_injustificada',
            'suspension_contrato' => self::OPERACION_DESCUENTO,
            default => self::OPERACION_DESCUENTO,
        };
    }

    public function resolverOperacionConContexto(string $tipoNovedad, array $data): string
    {
        $tipo = $this->normalizarTipoNovedad($tipoNovedad);

        if ($tipo === 'licencia_no_remunerada') {
            $esRemunerada = filter_var($data['es_remunerado'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return $esRemunerada ? self::OPERACION_DEVENGADO : self::OPERACION_DESCUENTO;
        }

        return $this->resolverOperacion($tipo);
    }

    private function normalizarTipoNovedad(string $tipoNovedad): string
    {
        $normalized = strtolower(trim($tipoNovedad));
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $normalized);

        return match ($normalized) {
            'incapacidad', 'incapacidad_enfermedad', 'incapacidad_enfermedad_general' => 'incapacidad_enfermedad_general',
            'incapacidad_laboral', 'incapacidad_laboral_arl' => 'incapacidad_laboral_arl',
            'licencia_maternidad' => 'licencia_maternidad',
            'licencia_paternidad' => 'licencia_paternidad',
            'licencia_por_luto', 'licencia_luto' => 'licencia_luto',
            'licencia_remunerada' => 'licencia_remunerada',
            'licencia', 'licencia_no_remunerada' => 'licencia_no_remunerada',
            'calamidad_domestica' => 'calamidad_domestica',
            'cita_medica' => 'cita_medica',
            'permiso_remunerado' => 'permiso_remunerado',
            'permiso', 'permiso_no_remunerado' => 'permiso_no_remunerado',
            'ausencia_injustificada' => 'ausencia_injustificada',
            'suspension', 'suspension_contrato' => 'suspension_contrato',
            default => str_replace(' ', '_', $normalized),
        };
    }
}
