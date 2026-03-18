<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class PlanService
{
    /**
     * Check if a tenant can add a new employee or process a payroll based on their plan.
     * 
     * @param Empresa $empresa
     * @return array ['can' => bool, 'reason' => string|null]
     */
    public function checkEmployeeLimit(Empresa $empresa): array
    {
        $licencia = $empresa->licencia;
        
        if (!$licencia) {
            return [
                'can' => false,
                'reason' => 'No se encontró una licencia activa para esta empresa.'
            ];
        }

        $plan = $licencia->plan;
        if (!$plan) {
            return [
                'can' => false,
                'reason' => 'No hay un plan asociado a la licencia actual.'
            ];
        }

        $currentCount = $empresa->activeEmployeesCount();
        $limit = (int) $plan->num_empl;

        if ($limit > 0 && $currentCount >= $limit) {
            Log::warning("Límite de plan alcanzado para Empresa ID: {$empresa->id_empresa}. Límite: {$limit}, Actual: {$currentCount}");
            return [
                'can' => false,
                'reason' => "Has alcanzado el límite de empleados de tu Plan {$plan->nombre} ({$limit} empleados). Por favor, actualiza tu plan para continuar."
            ];
        }

        return ['can' => true, 'reason' => null];
    }

    /**
     * Check if a tenant can add a new admin user.
     */
    public function checkAdminLimit(Empresa $empresa): array
    {
        $licencia = $empresa->licencia;
        if (!$licencia || !$licencia->plan) return ['can' => true, 'reason' => null];

        $plan = $licencia->plan;
        $currentCount = $empresa->adminsCount();
        $limit = (int) $plan->max_admins;

        if ($limit > 0 && $currentCount >= $limit) {
            return [
                'can' => false,
                'reason' => "Límite de administradores alcanzado para tu plan ({$limit})."
            ];
        }

        return ['can' => true, 'reason' => null];
    }

    /**
     * Check if a tenant can add a new auxiliary user.
     */
    public function checkAuxiliaryLimit(Empresa $empresa): array
    {
        $licencia = $empresa->licencia;
        if (!$licencia || !$licencia->plan) return ['can' => true, 'reason' => null];

        $plan = $licencia->plan;
        $currentCount = $empresa->auxiliariesCount();
        $limit = (int) $plan->max_auxiliares;

        if ($currentCount >= $limit) {
            return [
                'can' => false,
                'reason' => "Límite de auxiliares de nómina alcanzado para tu plan ({$limit})."
            ];
        }

        return ['can' => true, 'reason' => null];
    }

    /**
     * Future check for electronic payroll credits.
     */
    public function hasCreditsForElectronicPayroll(Empresa $empresa): bool
    {
        return true;
    }
}
