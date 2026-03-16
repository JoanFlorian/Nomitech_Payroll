<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
                $hasEstadoLaboral = Schema::hasColumn('contrato', 'estado_laboral');
                $hasEstadoNomina = Schema::hasColumn('contrato', 'estado_nomina');

                // Filter via 'contrato' relationship using new contractual states
                // Visible if: Active Laboral State OR Pending Payroll State
                $builder->whereHas('contratos', function ($query) use ($empresaId) {
                    $query->where('id_empresa', $empresaId)
                        ->where(function ($q) {
                            $q->where('estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO)
                                ->orWhere('estado_nomina', Contrato::ESTADO_NOMINA_PENDIENTE);
                        });
                        return;
                    }

                    // Backward compatibility for older schemas.
                    $query->where('activo', 1);
                });
            }
        });
    }
}
