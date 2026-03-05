<?php

namespace App\Services;

use App\Models\Salario;

class CalculoNovedadService
{
    public function obtenerSalarioEmpleado(string $empleadoId): ?Salario
    {
        return Salario::query()
            ->join('contrato', 'contrato.id_contrato', '=', 'salario.id_contrato')
            ->where('contrato.doc', $empleadoId)
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
        if (isset($data['pago_manual']) && $data['pago_manual'] !== null && $data['pago_manual'] !== '') {
            return (float) $data['pago_manual'];
        }

        $tipo = strtolower((string) ($data['tipo_novedad'] ?? ''));
        $dias = (float) ($data['dias'] ?? 0);
        $horas = (float) ($data['horas'] ?? 0);
        $esRemunerado = (bool) ($data['es_remunerado'] ?? false);

        $salarioDia = $salarioBase / 30;
        $salarioHora = $salarioBase / 240;

        return match ($tipo) {
            'licencia' => $esRemunerado ? 0 : -($salarioDia * $dias),
            'permiso' => $esRemunerado ? 0 : -(($horas > 0) ? ($salarioHora * $horas) : ($salarioDia * $dias)),
            'suspensión', 'suspension' => -($salarioDia * $dias),
            'incapacidad' => -(($salarioDia * 0.6667) * $dias),
            default => 0,
        };
    }
}
