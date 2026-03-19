<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Models\Salario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NominaCalculatorService
{
    private $params;
    private SecuritySocialCalculator $securitySocialCalculator;

    public function __construct(
        NominaParameterService $paramService,
        SecuritySocialCalculator $securitySocialCalculator
    )
    {
        $this->params = $paramService->get();
        $this->securitySocialCalculator = $securitySocialCalculator;
    }

    public function calcularValorHora(float $salarioBase): float
    {
        return $salarioBase / $this->params->horas_mes;
    }

    public function calcularContribuciones(float $salarioBase): array
    {
        $eps = $salarioBase * $this->params->eps_employee;
        $afp = $salarioBase * $this->params->pension_employee;
        $aportesEmpresa = $this->securitySocialCalculator->calculate($salarioBase, $this->params);
        $arl = (float) ($aportesEmpresa['aporte_arl'] ?? 0);

        $seguridadSocial = $eps + $afp;

        $aporteFp = 0;

        if ($salarioBase >= ($this->params->smmlv * 4)) {
            $aporteFp = $salarioBase * $this->params->fondo_solidaridad;
        }

        return [
            'eps' => $eps,
            'afp' => $afp,
            'arl' => $arl,
            'caja_compensacion' => (float) ($aportesEmpresa['aporte_caja'] ?? 0),
            'aporte_salud_empresa' => (float) ($aportesEmpresa['aporte_salud'] ?? 0),
            'aporte_pension_empresa' => (float) ($aportesEmpresa['aporte_pension'] ?? 0),
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

    public function calcularNominaEmpleado(int $idContrato, int $idPeriodo, array $input = []): array
    {
        $contrato = Contrato::query()->with('nivelRiesgo')->findOrFail($idContrato);
        $periodo = PeriodoLiquidacion::query()->findOrFail($idPeriodo);

        if ((int) $contrato->id_empresa !== (int) $periodo->id_empresa) {
            throw new \InvalidArgumentException('El contrato no pertenece a la empresa del periodo de nómina.');
        }

        $salarioBase = (float) ($contrato->salario_base ?? 0);
        $valorHora = $salarioBase > 0 ? ($salarioBase / 240) : 0;

        $resumenNovedades = $this->resumirNovedadesContratoPeriodo($idContrato, $idPeriodo, $valorHora);

        $diasSln = (int) ($resumenNovedades['dias_sln'] ?? 0);
        $diasTrabajados = max(0, min(30, (int) ($input['dias_trabajados'] ?? 30) - $diasSln));
        $salarioDevengado = ($salarioBase / 30) * $diasTrabajados;

        $baseHorasExtra = max(0, (float) ($input['horas_extra'] ?? 0));
        $baseRecargos = max(0, (float) ($input['recargos'] ?? 0));
        $baseBonificaciones = max(0, (float) ($input['bonificaciones'] ?? 0));
        $baseComisiones = max(0, (float) ($input['comisiones'] ?? 0));
        $baseOtrosDevengos = max(0, (float) ($input['otros_devengos'] ?? 0));
        $auxilioTransporte = max(0, (float) ($input['auxilio_transporte'] ?? 0));

        $retencionFuente = max(0, (float) ($input['retencion_fuente'] ?? 0));
        $embargoFiscal = max(0, (float) ($input['embargo_fiscal'] ?? 0));
        $pensionVoluntaria = max(0, (float) ($input['pension_voluntaria'] ?? 0));



        $horasExtra = $baseHorasExtra + (float) ($resumenNovedades['horas_extra'] ?? 0);
        $recargos = $baseRecargos + (float) ($resumenNovedades['recargos'] ?? 0);
        $bonificaciones = $baseBonificaciones + (float) ($resumenNovedades['bonificaciones'] ?? 0);
        $comisiones = $baseComisiones;
        $otrosDevengos = $baseOtrosDevengos + (float) ($resumenNovedades['otros_devengos'] ?? 0);

        $devengosSinRecargos = $this->calcularDevengos(
            $salarioDevengado,
            $horasExtra,
            $bonificaciones,
            $comisiones
        );

        $totalDevengado = $devengosSinRecargos + $recargos + $otrosDevengos + $auxilioTransporte;

        $epsRate = (float) ($this->params->eps_employee ?? 0.04);
        $afpRate = (float) ($this->params->pension_employee ?? 0.04);
        $eps = $totalDevengado * $epsRate;
        $afp = $totalDevengado * $afpRate;
        $seguridadSocial = $eps + $afp;

        // IBC para ARL: salario base del contrato.
        $ibcArl = max(0, (float) ($contrato->salario_base ?? 0));
        $arlRate = $this->resolveArlRateFromContrato($contrato);
        $arl = round($ibcArl * $arlRate, 2);

        $aportesEmpresa = $this->securitySocialCalculator->calculate(
            $totalDevengado,
            $this->params
        );

        $totalDeducciones =
            $seguridadSocial
            + $retencionFuente
            + $embargoFiscal
            + $pensionVoluntaria
            + (float) ($resumenNovedades['deducciones'] ?? 0);

        $netoPagar = $this->calcularNeto($totalDevengado, $totalDeducciones);

        return [
            'id_contrato' => $idContrato,
            'id_periodo' => $idPeriodo,
            'fecha_pago' => $input['fecha_pago'] ?? now()->toDateString(),
            'dias_a_trabajar' => $diasTrabajados,
            'horas_extra' => $horasExtra,
            'valor_horas_extras_recargos' => $horasExtra + $recargos,
            'auxilio_transporte' => $auxilioTransporte,
            'bonificaciones' => $bonificaciones,
            'comisiones' => $comisiones,
            'otros_devengos' => $otrosDevengos,
            'eps' => $eps,
            'afp' => $afp,
            'arl' => $arl,
            'aporte_salud_empresa' => (float) ($aportesEmpresa['aporte_salud'] ?? 0),
            'aporte_pension_empresa' => (float) ($aportesEmpresa['aporte_pension'] ?? 0),
            'seguridad_social' => $seguridadSocial,
            'aporte_fp' => 0,
            'retencion_fuente' => $retencionFuente,
            'embargo_fiscal' => $embargoFiscal,
            'pension_voluntaria' => $pensionVoluntaria,
            'caja_compensacion' => (float) ($aportesEmpresa['aporte_caja'] ?? 0),
            'total_devengado' => $totalDevengado,
            'total_deducciones' => $totalDeducciones,
            'neto_pagar' => $netoPagar,
            'total_novedades_devengado' =>
                (float) ($resumenNovedades['horas_extra'] ?? 0)
                + (float) ($resumenNovedades['recargos'] ?? 0)
                + (float) ($resumenNovedades['bonificaciones'] ?? 0)
                + (float) ($resumenNovedades['otros_devengos'] ?? 0),
            'total_novedades_deduccion' => (float) ($resumenNovedades['deducciones'] ?? 0),
            'resumen_novedades' => $resumenNovedades,
            'valor_hora' => $valorHora,
            'salario_base' => $salarioBase,
        ];
    }

    private function resolveArlRateFromContrato(Contrato $contrato): float
    {
        $porcentaje = $contrato->nivelRiesgo?->porcentaje;

        if ($porcentaje === null) {
            return 0.0;
        }

        $rate = max(0, (float) $porcentaje);

        // Soporta ambos formatos de almacenamiento:
        // - Fracción decimal: 0.02436
        // - Porcentaje humano: 2.436
        if ($rate > 0.1) {
            $rate = $rate / 100;
        }

        return $rate;
    }

    public function guardarNominaEmpleado(int $idContrato, int $idPeriodo, array $input = []): Salario
    {
        $calculo = $this->calcularNominaEmpleado($idContrato, $idPeriodo, $input);

        $payload = [
            'fecha_pago' => $calculo['fecha_pago'],
            'horas_extra' => $calculo['horas_extra'],
            'valor_horas_extras_recargos' => $calculo['valor_horas_extras_recargos'],
            'auxilio_transporte' => $calculo['auxilio_transporte'],
            'bonificaciones' => $calculo['bonificaciones'],
            'comisiones' => $calculo['comisiones'],
            'otros_devengos' => $calculo['otros_devengos'],
            'eps' => $calculo['eps'],
            'afp' => $calculo['afp'],
            'arl' => $calculo['arl'],
            'seguridad_social' => $calculo['seguridad_social'],
            'aporte_fp' => $calculo['aporte_fp'],
            'retencion_fuente' => $calculo['retencion_fuente'],
            'embargo_fiscal' => $calculo['embargo_fiscal'],
            'pension_voluntaria' => $calculo['pension_voluntaria'],
            'caja_compensacion' => $calculo['caja_compensacion'],
            'dias_a_trabajar' => $calculo['dias_a_trabajar'],
            'total_devengado' => $calculo['total_devengado'],
            'total_deducciones' => $calculo['total_deducciones'],
            'neto_pagar' => $calculo['neto_pagar'],
            'estado' => Salario::ESTADO_PENDIENTE,
        ];

        return DB::transaction(function () use ($idContrato, $idPeriodo, $payload) {
            $salario = Salario::query()
                ->where('id_contrato', $idContrato)
                ->where('id_periodo', $idPeriodo)
                ->first();

            if (!$salario) {
                $salario = new Salario();
                $salario->id_contrato = $idContrato;
                $salario->id_periodo = $idPeriodo;
            }

            if (
                $salario->exists &&
                in_array((string) $salario->estado, [Salario::ESTADO_LIQUIDADO, Salario::ESTADO_PAGADO], true)
            ) {
                return $salario;
            }

            foreach ($payload as $column => $value) {
                $salario->{$column} = $value;
            }
            $salario->save();

            return $salario;
        });
    }

    private function resumirNovedadesContratoPeriodo(int $idContrato, int $idPeriodo, float $valorHora): array
    {
        $resumen = [
            'horas_extra' => 0.0,
            'recargos' => 0.0,
            'bonificaciones' => 0.0,
            'otros_devengos' => 0.0,
            'deducciones' => 0.0,
            'dias_sln' => 0,
        ];

        $periodo = PeriodoLiquidacion::query()->find($idPeriodo);
        if (!$periodo) {
            return $resumen;
        }

        $fechaInicio = optional($periodo->fecha_inicio)->toDateString();
        $fechaFin = optional($periodo->fecha_fin)->toDateString();

        $tiposRecargo = DB::table('tipo_hora_recargo')
            ->select('nombre', 'valor')
            ->get();

        $novedades = DB::table('novedad as n')
            ->join('salario as s', 's.id_salario', '=', 'n.id_salario')
            ->where('s.id_contrato', $idContrato)
            ->where(function ($query) use ($idPeriodo, $fechaInicio, $fechaFin) {
                $query->where('n.id_periodo', $idPeriodo)
                    ->orWhere(function ($q) use ($idPeriodo) {
                        $q->whereNull('n.id_periodo')
                            ->where('s.id_periodo', $idPeriodo);
                    })
                    ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                        $q->whereNotNull('n.fecha_inicio')
                            ->whereNotNull('n.fecha_fin')
                            ->whereDate('n.fecha_inicio', '<=', $fechaFin)
                            ->whereDate('n.fecha_fin', '>=', $fechaInicio);
                    })
                    ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                        $q->whereNotNull('n.fecha')
                            ->whereBetween(DB::raw('DATE(n.fecha)'), [$fechaInicio, $fechaFin]);
                    });
            })
            ->select([
                'n.pago',
                'n.horas',
                'n.cantidad',
                'n.dias',
                'n.unidad_cantidad',
                'n.tipo_novedad_codigo',
                'n.tipo_novedad_nombre',
                'n.fecha_inicio',
                'n.fecha_fin',
            ])
            ->get();

        foreach ($novedades as $novedad) {
            $codigo = strtoupper(trim((string) ($novedad->tipo_novedad_codigo ?? '')));

            // Tipos de novedad que reducen los días trabajados efectivos
            $tiposQueRestanDias = ['SLN', 'IGE', 'IRL', 'IRP', 'LMA', 'LMAT', 'LPAT', 'VAC'];

            if (in_array($codigo, $tiposQueRestanDias, true)) {
                $novInicio = $novedad->fecha_inicio ? \Carbon\Carbon::parse($novedad->fecha_inicio) : null;
                $novFin = $novedad->fecha_fin ? \Carbon\Carbon::parse($novedad->fecha_fin) : null;
                $pInicio = \Carbon\Carbon::parse($fechaInicio);
                $pFin = \Carbon\Carbon::parse($fechaFin);

                if ($novInicio && $novFin) {
                    $efectivoInicio = $novInicio->greaterThan($pInicio) ? $novInicio : $pInicio;
                    $efectivoFin = $novFin->lessThan($pFin) ? $novFin : $pFin;
                    $diasEnPeriodo = max(0, $efectivoInicio->diffInDays($efectivoFin) + 1);
                    $resumen['dias_sln'] += (int) $diasEnPeriodo;
                } else {
                    // Fallback: usar campo dias directamente
                    $resumen['dias_sln'] += (int) ($novedad->dias ?? $novedad->cantidad ?? 0);
                }
                
                // Si es SLN, no se suma a otros devengos/deducciones (ya manejado por reducción de días)
                // Para los otros tipos (IGE, VAC, etc.), el pago se manejará después en el loop
                if ($codigo === 'SLN') {
                    continue;
                }
            }

            $valor = (float) ($novedad->pago ?? 0);
            $texto = mb_strtolower(trim(((string) ($novedad->tipo_novedad_codigo ?? '')) . ' ' . ((string) ($novedad->tipo_novedad_nombre ?? ''))), 'UTF-8');

            if ($valor < 0) {
                $resumen['deducciones'] += abs($valor);
                continue;
            }

            if ($valor === 0.0) {
                continue;
            }

            $horas = (float) ($novedad->horas ?? 0);
            if ($horas <= 0 && strtolower((string) ($novedad->unidad_cantidad ?? '')) === 'horas') {
                $horas = (float) ($novedad->cantidad ?? 0);
            }

            if ($horas > 0 && (str_contains($texto, 'extra') || str_contains($texto, 'recargo'))) {
                $factor = $this->resolverFactorTipoRecargo($texto, $tiposRecargo);
                $montoCalculado = $horas * max(0, $valorHora) * max(0, $factor);

                if (str_contains($texto, 'recargo')) {
                    $resumen['recargos'] += $montoCalculado > 0 ? $montoCalculado : $valor;
                } else {
                    $resumen['horas_extra'] += $montoCalculado > 0 ? $montoCalculado : $valor;
                }
                continue;
            }

            if (str_contains($texto, 'extra')) {
                $resumen['horas_extra'] += $valor;
                continue;
            }

            if (str_contains($texto, 'recargo')) {
                $resumen['recargos'] += $valor;
                continue;
            }

            if (preg_match('/bonif|bono|incentivo|premio/u', $texto)) {
                $resumen['bonificaciones'] += $valor;
                continue;
            }

            $resumen['otros_devengos'] += $valor;
        }

        return $resumen;
    }

    private function resolverFactorTipoRecargo(string $texto, Collection $tiposRecargo): float
    {
        foreach ($tiposRecargo as $tipo) {
            $nombreTipo = mb_strtolower(trim((string) ($tipo->nombre ?? '')), 'UTF-8');
            if ($nombreTipo !== '' && str_contains($texto, $nombreTipo)) {
                return $this->normalizarMultiplicador((string) ($tipo->nombre ?? ''), (float) ($tipo->valor ?? 0));
            }
        }

        return $this->normalizarMultiplicador($texto, 0);
    }

    private function normalizarMultiplicador(string $nombre, float $valorCrudo): float
    {
        $nombreNormalizado = mb_strtolower(trim($nombre), 'UTF-8');

        if (str_contains($nombreNormalizado, 'hora extra diurna dominical') || str_contains($nombreNormalizado, 'hora extra diurna festiva')) {
            return 2.00;
        }

        if (str_contains($nombreNormalizado, 'hora extra nocturna dominical') || str_contains($nombreNormalizado, 'hora extra nocturna festiva')) {
            return 2.50;
        }

        if (str_contains($nombreNormalizado, 'hora extra nocturna')) {
            return 1.75;
        }

        if (str_contains($nombreNormalizado, 'hora extra diurna')) {
            return 1.25;
        }

        if (str_contains($nombreNormalizado, 'recargo nocturno')) {
            return 0.35;
        }

        if (str_contains($nombreNormalizado, 'recargo dominical') || str_contains($nombreNormalizado, 'recargo festivo')) {
            return 0.75;
        }

        if ($valorCrudo <= 0) {
            return 0.0;
        }

        if (str_contains($nombreNormalizado, 'recargo')) {
            return $valorCrudo > 1 ? ($valorCrudo / 100) : $valorCrudo;
        }

        if (str_contains($nombreNormalizado, 'extra')) {
            return $valorCrudo > 1 ? (1 + ($valorCrudo / 100)) : $valorCrudo;
        }

        return $valorCrudo;
    }
}