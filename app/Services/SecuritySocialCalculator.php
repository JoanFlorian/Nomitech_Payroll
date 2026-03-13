<?php

namespace App\Services;

class SecuritySocialCalculator
{
    public function calculate(float $ibc, object|array $config, int $riskLevel = 1): array
    {
        $ibc = round(max(0, $ibc), 2);

        return [
            'aporte_salud' => round($ibc * $this->getConfigValue($config, 'eps_employer', 0.085), 2),
            'aporte_pension' => round($ibc * $this->getConfigValue($config, 'pension_employer', 0.12), 2),
            'aporte_arl' => round($ibc * $this->resolveArlRate($config, $riskLevel), 2),
            'aporte_caja' => round($ibc * $this->getConfigValue($config, 'caja_compensacion', 0.04), 2),
        ];
    }

    private function getConfigValue(object|array $config, string $key, float $default): float
    {
        if (is_array($config)) {
            return (float) ($config[$key] ?? $default);
        }

        return (float) ($config->{$key} ?? $default);
    }

    private function resolveArlRate(object|array $config, int $riskLevel): float
    {
        $key = 'arl_riesgo_' . max(1, $riskLevel);

        if (is_array($config) && array_key_exists($key, $config)) {
            return (float) $config[$key];
        }

        if (is_object($config) && isset($config->{$key})) {
            return (float) $config->{$key};
        }

        return $this->getConfigValue($config, 'arl_riesgo_1', 0.00522);
    }
}