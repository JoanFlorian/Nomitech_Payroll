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
                $hasEstado = Schema::hasColumn('contrato', 'estado');

                // Filter via 'contrato' relationship using new contractual states
                // Visible if: Active Laboral State OR Pending Payroll State
                $builder->whereHas('contratos', function ($query) use ($empresaId, $hasEstadoLaboral, $hasEstadoNomina, $hasEstado) {
                    $query->where('id_empresa', $empresaId);

                    if ($hasEstadoLaboral || $hasEstadoNomina) {
                        $query->where(function ($q) use ($hasEstadoLaboral, $hasEstadoNomina) {
                            if ($hasEstadoLaboral) {
                                $q->where('estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO);
                            }

                            if ($hasEstadoNomina) {
                                $method = $hasEstadoLaboral ? 'orWhere' : 'where';
                                $q->{$method}('estado_nomina', Contrato::ESTADO_NOMINA_PENDIENTE);
                            }
                        });
                        return;
                    }

                    if ($hasEstado) {
                        $query->whereIn('estado', [
                            Contrato::ESTADO_ACTIVO,
                            Contrato::ESTADO_POR_VENCER,
                            Contrato::ESTADO_PROGRAMADO,
                            Contrato::ESTADO_VENCIDO,
                        ]);
                        return;
                    }

                    // Backward compatibility for older schemas.
                    $query->where('activo', 1);
                });
            }
        });
    }
}
