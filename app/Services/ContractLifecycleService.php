<?php

namespace App\Services;

use App\Models\Contrato;
use Carbon\Carbon;

class ContractLifecycleService
{
    /**
     * Evalúa si existe continuidad laboral entre el nuevo contrato
     * y el último contrato del empleado en la misma empresa.
     *
     * @param string $doc
     * @param int    $empresaId
     * @param Carbon $fechaInicioNuevo
     * @return array{has_continuity: bool, previous_contract: ?Contrato, gap_days: ?int}
     */
    public function evaluarContinuidad(string $doc, int $empresaId, Carbon $fechaInicioNuevo): array
    {
        $anterior = Contrato::where('doc', $doc)
            ->where('id_empresa', $empresaId)
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$anterior || !$anterior->fecha_fin) {
            return [
                'has_continuity' => false,
                'previous_contract' => $anterior,
                'gap_days' => null,
            ];
        }

        $gap = Carbon::parse($anterior->fecha_fin)->diffInDays($fechaInicioNuevo, false);

        return [
            'has_continuity' => $gap <= Contrato::CONTINUIDAD_DIAS_TOLERANCIA,
            'previous_contract' => $anterior,
            'gap_days' => $gap,
        ];
    }

    /**
     * Validaciones previas a la creación de un nuevo contrato.
     *
     * @throws \Exception
     */
    public function validarNuevoContrato(string $doc, int $empresaId, Carbon $fechaInicio, ?Carbon $fechaFin): void
    {
        // 1. fecha_inicio < fecha_fin
        if ($fechaFin && $fechaInicio->gte($fechaFin)) {
            throw new \Exception('La fecha de inicio debe ser anterior a la fecha de fin del contrato.');
        }

        // 2. No superposición con contratos vigentes
        $superpuesto = Contrato::where('doc', $doc)
            ->where('id_empresa', $empresaId)
            ->whereIn('estado', [
                Contrato::ESTADO_ACTIVO,
                Contrato::ESTADO_POR_VENCER,
                Contrato::ESTADO_PROGRAMADO,
            ])
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->where(function ($inner) use ($fechaInicio, $fechaFin) {
                    $inner->where('fecha_inicio', '<=', $fechaFin ?? Carbon::maxValue())
                          ->where(function ($fin) use ($fechaInicio) {
                              $fin->where('fecha_fin', '>=', $fechaInicio)
                                  ->orWhereNull('fecha_fin');
                          });
                });
            })
            ->exists();

        if ($superpuesto) {
            throw new \Exception('Ya existe un contrato vigente con fechas superpuestas para este empleado.');
        }
    }

    /**
     * Procesa la creación de un nuevo contrato: evalúa continuidad
     * y marca el anterior como VENCIDO si no hay continuidad.
     *
     * Se llama DESPUÉS de crear el nuevo contrato.
     */
    public function procesarCreacionContrato(Contrato $nuevoContrato): array
    {
        // Buscar el contrato anterior (excluyendo el recién creado)
        $anterior = Contrato::where('doc', $nuevoContrato->doc)
            ->where('id_empresa', $nuevoContrato->id_empresa)
            ->where('id_contrato', '!=', $nuevoContrato->id_contrato)
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$anterior) {
            return [
                'has_continuity' => false,
                'previous_contract' => null,
                'gap_days' => null,
            ];
        }

        if (!$anterior->fecha_fin) {
            return [
                'has_continuity' => false,
                'previous_contract' => $anterior,
                'gap_days' => null,
            ];
        }

        $gap = Carbon::parse($anterior->fecha_fin)
            ->diffInDays(Carbon::parse($nuevoContrato->fecha_inicio), false);

        $hasContinuity = $gap <= Contrato::CONTINUIDAD_DIAS_TOLERANCIA;

        // Si NO hay continuidad → marcar anterior como VENCIDO
        if (!$hasContinuity && $anterior->estado !== Contrato::ESTADO_TERMINADO) {
            $anterior->estado = Contrato::ESTADO_VENCIDO;
            $anterior->saveQuietly();
        }

        return [
            'has_continuity' => $hasContinuity,
            'previous_contract' => $anterior,
            'gap_days' => $gap,
        ];
    }

    /**
     * Sincroniza el estado de todos los contratos de una empresa.
     * Útil para un comando artisan programado o un job.
     */
    public function syncEstadosEmpresa(int $empresaId): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection|Contrato[] $contratos */
        $contratos = Contrato::where('id_empresa', $empresaId)
            ->where('estado', '!=', Contrato::ESTADO_TERMINADO)
            ->get();

        $updated = 0;
        foreach ($contratos as $contrato) {
            $antes = $contrato->estado;
            $contrato->syncEstado();
            if ($contrato->estado !== $antes) {
                $updated++;
            }
        }

        return $updated;
    }
}

