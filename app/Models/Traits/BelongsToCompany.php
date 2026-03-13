<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Contrato;

trait BelongsToCompany
{
    /**
     * Boot the trait and apply the Global Scope.
     */
    protected static function booted()
    {
        static::addGlobalScope('company_isolation', function (Builder $builder) {
            // Register the scope but only apply logic if there is an active session and not in console
            if (app()->runningInConsole() || !auth()->check()) {
                return;
            }

            $empresaId = session('empresa_id');

            if ($empresaId) {
                // Filter via 'contrato' relationship using new contractual states
                // Visible if: Active Laboral State OR Pending Payroll State
                $builder->whereHas('contratos', function ($query) use ($empresaId) {
                    $query->where('id_empresa', $empresaId)
                        ->where(function ($q) {
                            $q->whereIn('estado', [
                                Contrato::ESTADO_ACTIVO,
                                Contrato::ESTADO_POR_VENCER,
                                Contrato::ESTADO_PROGRAMADO,
                                Contrato::ESTADO_VENCIDO,
                            ]);
                        });
                });
            }
        });
    }
}
