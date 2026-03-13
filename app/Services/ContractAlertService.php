<?php

namespace App\Services;

use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContractAlertService
{
    /**
     * Contratos que vencen en los próximos 30 días.
     * Excluye los que ya tienen renovación creada.
     */
    public function getContractsExpiringSoon(int $empresaId): Collection
    {
        $hoy = Carbon::today();
        $limite = $hoy->copy()->addDays(Contrato::CONTINUIDAD_DIAS_TOLERANCIA);

        return Contrato::where('id_empresa', $empresaId)
            ->whereNotNull('fecha_fin')
            ->whereBetween('fecha_fin', [$hoy, $limite])
            ->whereIn('estado', [Contrato::ESTADO_ACTIVO, Contrato::ESTADO_POR_VENCER])
            // Excluir si ya existe un contrato futuro (renovación hecha)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('contrato as c2')
                    ->whereColumn('c2.doc', 'contrato.doc')
                    ->whereColumn('c2.id_empresa', 'contrato.id_empresa')
                    ->whereColumn('c2.id_contrato', '!=', 'contrato.id_contrato')
                    ->whereColumn('c2.fecha_inicio', '>', 'contrato.fecha_fin');
            })
            ->with('usuario')
            ->orderBy('fecha_fin')
            ->get();
    }

    /**
     * Contratos vencidos con liquidaciones pendientes.
     * Excluye los que tienen continuidad laboral (nuevo contrato con gap ≤ 30).
     */
    public function getContractsPendingLiquidation(int $empresaId): Collection
    {
        $hoy = Carbon::today();

        return Contrato::where('id_empresa', $empresaId)
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', $hoy)
            ->where('estado', Contrato::ESTADO_VENCIDO)
            ->where(function ($q) {
                $q->whereNull('salario_final_pagado_at')
                  ->orWhereNull('prestaciones_liquidadas_at')
                  ->orWhereNull('cesantias_transferidas_at')
                  ->orWhereNull('vacaciones_liquidadas_at');
            })
            // Excluir si tiene continuidad laboral (nuevo contrato con gap ≤ 30)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('contrato as c2')
                    ->whereColumn('c2.doc', 'contrato.doc')
                    ->whereColumn('c2.id_empresa', 'contrato.id_empresa')
                    ->whereColumn('c2.id_contrato', '!=', 'contrato.id_contrato')
                    ->whereColumn('c2.fecha_inicio', '>', 'contrato.fecha_fin')
                    ->whereRaw('DATEDIFF(c2.fecha_inicio, contrato.fecha_fin) <= ?', [
                        Contrato::CONTINUIDAD_DIAS_TOLERANCIA,
                    ]);
            })
            ->with('usuario')
            ->orderBy('fecha_fin')
            ->get();
    }

    /**
     * Resumen de alertas formateado para la vista.
     *
     * @return array{expiring: array, pending_liquidation: array}
     */
    public function getAlertSummary(int $empresaId): array
    {
        $expiring = $this->getContractsExpiringSoon($empresaId);
        $pending = $this->getContractsPendingLiquidation($empresaId);

        return [
            'expiring' => $this->formatExpiringAlerts($expiring),
            'pending_liquidation' => $this->formatPendingAlerts($pending),
        ];
    }

    /**
     * Formatea alertas de contratos por vencer.
     */
    private function formatExpiringAlerts(Collection $contratos): array
    {
        if ($contratos->isEmpty()) {
            return ['count' => 0, 'message' => null, 'items' => []];
        }

        $count = $contratos->count();
        $hoy = Carbon::today();

        if ($count === 1) {
            $contrato = $contratos->first();
            $nombre = $this->getNombreEmpleado($contrato);
            $dias = $hoy->diffInDays($contrato->fecha_fin, false);
            $message = "El contrato de {$nombre} vence en {$dias} día(s). Recuerde informar al empleado si el contrato será renovado.";
        } else {
            $message = "{$count} contratos vencerán en los próximos 30 días. Revise las renovaciones pendientes.";
        }

        return [
            'count' => $count,
            'message' => $message,
            'items' => $contratos->map(function ($c) use ($hoy) {
                return [
                    'id_contrato' => $c->id_contrato,
                    'doc' => $c->doc,
                    'nombre' => $this->getNombreEmpleado($c),
                    'fecha_fin' => $c->fecha_fin->format('Y-m-d'),
                    'dias_restantes' => $hoy->diffInDays($c->fecha_fin, false),
                ];
            })->toArray(),
        ];
    }

    /**
     * Formatea alertas de liquidaciones pendientes.
     */
    private function formatPendingAlerts(Collection $contratos): array
    {
        if ($contratos->isEmpty()) {
            return ['count' => 0, 'message' => null, 'items' => []];
        }

        $count = $contratos->count();

        if ($count === 1) {
            $contrato = $contratos->first();
            $nombre = $this->getNombreEmpleado($contrato);
            $message = "El contrato de {$nombre} finalizó y tiene liquidaciones pendientes.";
        } else {
            $message = "Hay {$count} contratos vencidos con liquidaciones pendientes.";
        }

        return [
            'count' => $count,
            'message' => $message,
            'items' => $contratos->map(function ($c) {
                return [
                    'id_contrato' => $c->id_contrato,
                    'doc' => $c->doc,
                    'nombre' => $this->getNombreEmpleado($c),
                    'fecha_fin' => $c->fecha_fin->format('Y-m-d'),
                    'pendientes' => $c->pendientes,
                ];
            })->toArray(),
        ];
    }

    /**
     * Obtiene el nombre completo del empleado desde la relación.
     */
    private function getNombreEmpleado(Contrato $contrato): string
    {
        $usuario = $contrato->usuario;

        if (!$usuario) {
            return $contrato->doc;
        }

        return trim(
            ($usuario->primer_nombre ?? '') . ' ' .
            ($usuario->primer_apellido ?? '')
        ) ?: $contrato->doc;
    }
}
