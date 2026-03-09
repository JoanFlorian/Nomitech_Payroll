<?php

namespace App\Services;

class NominaCalculatorService
{
    private $params;

    public function __construct(NominaParameterService $paramService)
    {
        $this->params = $paramService->get();
    }

    public function calcularValorHora(float $salarioBase): float
    {
        return $salarioBase / $this->params->horas_mes;
    }

    public function calcularContribuciones(float $salarioBase): array
    {
        $eps = $salarioBase * $this->params->eps_employee;
        $afp = $salarioBase * $this->params->pension_employee;
        $arl = $salarioBase * $this->params->arl_riesgo_1;

        $seguridadSocial = $eps + $afp;

        $aporteFp = 0;

        if ($salarioBase >= ($this->params->smmlv * 4)) {
            $aporteFp = $salarioBase * $this->params->fondo_solidaridad;
        }

        return [
            'eps' => $eps,
            'afp' => $afp,
            'arl' => $arl,
            'seguridad_social' => $seguridadSocial,
            'aporte_fp' => $aporteFp,
        ];
    }

    public function calcularDevengos(float $salarioBase, float $extras, float $bonos, float $comisiones): float
    {
        return $salarioBase + $extras + $bonos + $comisiones;
    }

    public function calcularNeto(float $devengos, float $deducciones): float
    {
        return $devengos - $deducciones;
    }
}