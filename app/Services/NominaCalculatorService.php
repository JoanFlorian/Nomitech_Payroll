<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Novedad;
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

    public function calcularContribuciones(float $salarioBase, ?int $idTipoContrato = null, bool $tieneMaternidad = false): array
    {
        // Si está en licencia de maternidad, no se calculan contribuciones de seguridad social
        if ($tieneMaternidad) {
            return [
                'eps' => 0,
                'afp' => 0,
                'arl' => 0,
                'caja_compensacion' => 0,
                'aporte_salud_empresa' => 0,
                'aporte_pension_empresa' => 0,
                'seguridad_social' => 0,
                'aporte_fp' => 0,
            ];
        }

        if ($idTipoContrato === \App\Models\TipoContrato::TIPO_PRESTACION_SERVICIOS) {
            return [
                'eps' => 0,
                'afp' => 0,
                'arl' => 0,
                'caja_compensacion' => 0,
                'aporte_salud_empresa' => 0,
                'aporte_pension_empresa' => 0,
                'seguridad_social' => 0,
                'aporte_fp' => 0,
            ];
        }

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

        // ── Bloqueo por incapacidad activa ────────────────────────────────────────
        // Si el contrato tiene una IGE o IRL registrada en un periodo anterior que
        // aún cubre este periodo, el empleado NO puede ser liquidado:
        //   · IGE: la empresa solo paga los primeros 2 días (ya cubiertos en el
        //     periodo original). Los días restantes son responsabilidad de la EPS.
        //   · IRL: la ARL cubre desde el día 1; no corresponde liquidar al empleado
        //     en periodos en los que la incapacidad aún está vigente.
        $fechaInicioP = optional($periodo->fecha_inicio)->toDateString() ?? '';
        $fechaFinP    = optional($periodo->fecha_fin)->toDateString()    ?? '';
        if (Novedad::tieneIncapacidadActivaEnPeriodo($idContrato, $idPeriodo, $fechaInicioP, $fechaFinP)) {
            throw new \App\Exceptions\EmpleadoIncapacitadoException(
                'El empleado no puede ser liquidado porque tiene una incapacidad activa en este periodo.'
            );
        }
        // ─────────────────────────────────────────────────────────────────────────

        $salarioBase = (float) ($contrato->salario_base ?? 0);
        $valorHora = $salarioBase > 0 ? ($salarioBase / 240) : 0;

        $resumenNovedades = $this->resumirNovedadesContratoPeriodo($idContrato, $idPeriodo, $valorHora);

        $diasAusenciaTotal = (int) ($resumenNovedades['dias_ausencia_total'] ?? 0);
        $diasAusenciaPrestacional = (int) ($resumenNovedades['dias_ausencia_prestacional'] ?? 0);
        $diasLicenciaMaternidad = (int) ($resumenNovedades['dias_licencia_maternidad'] ?? 0);

        // Días a trabajar para el PAGO de nómina (resta todas las ausencias)
        $diasTrabajados = max(0, min(30, (int) ($input['dias_trabajados'] ?? 30) - $diasAusenciaTotal));
        
        // Cuando hay licencia de maternidad en el periodo, no se paga salario base.
        if ($diasLicenciaMaternidad > 0) {
            $diasTrabajados = 0;
        }

        // Días para PRESTACIONES sociales (solo resta SLN)
        $diasPrestacionales = max(0, min(30, (int) ($input['dias_trabajados'] ?? 30) - $diasAusenciaPrestacional));

        $salarioDevengado = $diasLicenciaMaternidad > 0 ? 0 : ($salarioBase / 30) * $diasTrabajados;

        $baseHorasExtra = max(0, (float) ($input['horas_extra'] ?? 0));
        $baseRecargos = max(0, (float) ($input['recargos'] ?? 0));
        $baseBonificaciones = max(0, (float) ($input['bonificaciones'] ?? 0));
        $baseComisiones = max(0, (float) ($input['comisiones'] ?? 0));
        $baseOtrosDevengos = max(0, (float) ($input['otros_devengos'] ?? 0));
        $auxilioTransporte = max(0, (float) ($input['auxilio_transporte'] ?? 0));

        // Centralized logic for Auxilio de Transporte recalculation/validation
        if (!empty($input['recalculate_transport_allowance'])) {
            $smmlv = (float) ($this->params->smmlv ?? 0);
            $topeAuxilio = (float) ($this->params->auxilio_transporte_tope ?? 0);
            $valorMensualAuxilio = (float) ($this->params->auxilio_transporte ?? 0);

            // Eligibility check ALWAYS uses the full monthly base (not the pro-rated one)
            $esElegible = ($smmlv > 0 && $topeAuxilio > 0)
                ? ($salarioBase <= ($smmlv * $topeAuxilio))
                : true;

            // Manual override: if the user explicitly said NOT to apply it, set to false
            if (isset($input['aplica_auxilio_transporte']) && (int)$input['aplica_auxilio_transporte'] === 0) {
                $esElegible = false;
            }

            if ($esElegible) {
                // Pro-rate based on worked days: (Monthly Amount / 30) * Days
                $auxilioTransporte = ($valorMensualAuxilio / 30) * $diasTrabajados;
            } else {
                $auxilioTransporte = 0;
            }
        }

        // Valores calculados de novedades
        $novHorasExtra = (float) ($resumenNovedades['horas_extra'] ?? 0);
        $novRecargos = (float) ($resumenNovedades['recargos'] ?? 0);
        $novBonificaciones = (float) ($resumenNovedades['bonificaciones'] ?? 0);
        $novOtrosDevengos = (float) ($resumenNovedades['otros_devengos'] ?? 0);
        $novDeducciones = (float) ($resumenNovedades['deducciones'] ?? 0);

        // Si el input viene de un origen persistido que puede estar "contaminado" con 
        // valores de novedades previos (debido al bug antiguo), intentamos limpiarlo.
        if (!empty($input['limpiar_novedades'])) {
            $baseHorasExtra = max(0, $baseHorasExtra - $novHorasExtra);
            $baseRecargos = max(0, $baseRecargos - $novRecargos);
            $baseBonificaciones = max(0, $baseBonificaciones - $novBonificaciones);
            $baseOtrosDevengos = max(0, $baseOtrosDevengos - $novOtrosDevengos);
        }

        $retencionFuente = max(0, (float) ($input['retencion_fuente'] ?? 0));
        $embargoFiscal = max(0, (float) ($input['embargo_fiscal'] ?? 0));
        $pensionVoluntaria = max(0, (float) ($input['pension_voluntaria'] ?? 0));

        // Totales combinados para el cálculo de seguridad social y neto
        // PERO mantenemos las variables individuales como "manuales" para el retorno/guardado
        $horasExtraTotal = $baseHorasExtra + $novHorasExtra;
        $recargosTotal = $baseRecargos + $novRecargos;
        $bonificacionesTotal = $baseBonificaciones + $novBonificaciones;
        // Incluir el valor de maternidad/paternidad en otros devengos
        $otrosDevengosTotal = $baseOtrosDevengos + $novOtrosDevengos + (float) ($resumenNovedades['valor_licencia_maternidad'] ?? 0);

        $devengosSinRecargos = $this->calcularDevengos(
            $salarioDevengado,
            $horasExtraTotal,
            $bonificacionesTotal,
            $baseComisiones
        );

        $totalDevengado = $devengosSinRecargos + $recargosTotal + $otrosDevengosTotal + $auxilioTransporte;

        // ── INTEGRATED BENEFITS (BenefitLedger) ──
        $integratedBenefitsList = DB::table('benefit_ledger')
            ->where('contract_id', $idContrato)
            ->where('payroll_period_id', $idPeriodo)
            ->where('movement_type', 'scheduled_payment')
            ->where('status', 'pending_payroll')
            ->get(['benefit_type', 'amount']);

        $integratedTotal = 0;
        foreach ($integratedBenefitsList as $ib) {
            $absAmount = abs((float) $ib->amount);
            $type = trim(strtolower($ib->benefit_type));
            
            // Skip Cesantías as requested by user - they should not affect payroll total
            if ($type === 'cesantias') {
                continue;
            }

            // Vacaciones are stored in DÍAS in the ledger. Convert to monetary value dynamically.
            // Formula: (Devenged Salary / 30) * Accumulated Days 
            // We use $salarioDevengado (pro-rated) to match user expectation for partial months (e.g. 456k vs 540k)
            if ($type === 'vacaciones') {
                $fechaFinContrato = $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin) : null;
                $fechaInicioPeriodo = \Carbon\Carbon::parse($periodo->fecha_inicio);
                $fechaFinPeriodo = \Carbon\Carbon::parse($periodo->fecha_fin);

                // USER RULE: Vacations ONLY integrated on termination in this period
                $isTermination = $fechaFinContrato && 
                                 $fechaFinContrato->between($fechaInicioPeriodo, $fechaFinPeriodo);

                if ($isTermination) {
                    $integratedTotal += round(($salarioDevengado / 30) * $absAmount, 2);
                }
            } else {
                // All other benefits (Prima, Intereses) are stored directly in monetary value
                $integratedTotal += $absAmount;
            }
        }

        $totalDevengado += $integratedTotal;

        $eps = 0;
        $afp = 0;
        $seguridadSocial = 0;
        $arl = 0;
        $aportesEmpresa = [];

        // Si el empleado está en licencia de maternidad, NO se calculan aportes de seguridad social
        if ($diasLicenciaMaternidad === 0 && (int) $contrato->id_tipo_contrato !== \App\Models\TipoContrato::TIPO_PRESTACION_SERVICIOS) {
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
        }

        $totalDeducciones =
            $seguridadSocial
            + $retencionFuente
            + $embargoFiscal
            + $pensionVoluntaria
            + $novDeducciones;

        $netoPagar = $this->calcularNeto($totalDevengado, $totalDeducciones);

        return [
            'id_contrato' => $idContrato,
            'id_periodo' => $idPeriodo,
            'fecha_pago' => $input['fecha_pago'] ?? now()->toDateString(),
            'dias_a_trabajar' => $diasTrabajados,
            'dias_trabajados_prestacional' => $diasPrestacionales,
            // IMPORTANTE: Estas variables 'horas_extra', 'otros_devengos', etc. 
            // ahora representan solo el componente MANUAL/BASE que se guardará en la tabla 'salario'.
            // El componente de NOVEDADES se suma dinámicamente en el modelo Salario (vía accessors) 
            // a partir de la tabla 'novedad', evitando duplicidad y acumulación infinita.
            'horas_extra' => $baseHorasExtra,
            'valor_horas_extras_recargos' => $baseHorasExtra + $baseRecargos,
            'auxilio_transporte' => $auxilioTransporte,
            'bonificaciones' => $baseBonificaciones,
            'comisiones' => $baseComisiones,
            'otros_devengos' => $baseOtrosDevengos,
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
            'prestaciones_sociales' => $integratedTotal,
            'total_novedades_devengado' => $novHorasExtra + $novRecargos + $novBonificaciones + $novOtrosDevengos,
            'total_novedades_deduccion' => $novDeducciones,
            'resumen_novedades' => $resumenNovedades,
            'dias_licencia_maternidad' => $diasLicenciaMaternidad,
            'valor_licencia_maternidad' => (float) ($resumenNovedades['valor_licencia_maternidad'] ?? 0),
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

    public function guardarNominaEmpleado(int $idContrato, int $idPeriodo, array $input = [], bool $force = false): Salario
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
            'dias_trabajados_prestacional' => $calculo['dias_trabajados_prestacional'],
            'total_devengado' => $calculo['total_devengado'],
            'total_deducciones' => $calculo['total_deducciones'],
            'neto_pagar' => $calculo['neto_pagar'],
            'prestaciones_sociales' => $calculo['prestaciones_sociales'],
            'estado' => Salario::ESTADO_PENDIENTE,
        ];

        return DB::transaction(function () use ($idContrato, $idPeriodo, $payload, $force) {
            $salario = Salario::query()
                ->where('id_contrato', $idContrato)
                ->where('id_periodo', $idPeriodo)
                ->first();

            if (!$salario) {
                $salario = new Salario();
                $salario->id_contrato = $idContrato;
                $salario->id_periodo = $idPeriodo;
                $salario->exists = false; // Ensure it's treated as new
            }

            if (
                !$force &&
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

    public function resumirNovedadesContratoPeriodo(int $idContrato, int $idPeriodo, float $valorHora): array
    {
        $resumen = [
            'horas_extra' => 0.0,
            'recargos' => 0.0,
            'bonificaciones' => 0.0,
            'otros_devengos' => 0.0,
            'deducciones' => 0.0,
            'dias_ausencia_total' => 0,
            'dias_ausencia_prestacional' => 0,
            'dias_licencia_maternidad' => 0,
            'valor_licencia_maternidad' => 0.0,
        ];

        $periodo = PeriodoLiquidacion::query()->find($idPeriodo);
        if (!$periodo) {
            return $resumen;
        }

        $fechaInicio = optional($periodo->fecha_inicio)->toDateString();
        $fechaFin = optional($periodo->fecha_fin)->toDateString();

        // 🔹 NUEVO: Procesar rollover de LMAT/LPAT del período anterior
        $this->procesarRollovertMaternidad($idContrato, $idPeriodo, $periodo, $valorHora, $resumen);

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
                        // IGE e IRL son restringidos al periodo de registro exacto.
                        // Solo se incluyen aqui novedades que NO son de tipo periodo-aislado.
                        $q->whereNotNull('n.fecha_inicio')
                            ->whereNotNull('n.fecha_fin')
                            ->whereDate('n.fecha_inicio', '<=', $fechaFin)
                            ->whereDate('n.fecha_fin', '>=', $fechaInicio)
                            ->whereNotIn('n.tipo_novedad_codigo', ['IGE', 'IRL']);
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
                'n.tipo_incapacidad',
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

                $diasEnPeriodo = 0;

                if ($novInicio && $novFin) {
                    $efectivoInicio = $novInicio->greaterThan($pInicio) ? $novInicio : $pInicio;
                    $efectivoFin = $novFin->lessThan($pFin) ? $novFin : $pFin;
                    $diasEnPeriodo = max(0, $efectivoInicio->diffInDays($efectivoFin) + 1);
                } elseif ($novInicio && !$novFin && (int) ($novedad->dias ?? 0) > 0) {
                    // Soporte para novedad de maternidad/parental con fecha_fin no explicitada
                    $novedadFin = $novInicio->copy()->addDays((int) ($novedad->dias ?? 0) - 1);
                    $efectivoInicio = $novInicio->greaterThan($pInicio) ? $novInicio : $pInicio;
                    $efectivoFin = $novedadFin->lessThan($pFin) ? $novedadFin : $pFin;
                    $diasEnPeriodo = max(0, $efectivoInicio->diffInDays($efectivoFin) + 1);
                } else {
                    // Fallback: usar campo dias directamente
                    $diasEnPeriodo = (int) ($novedad->dias ?? $novedad->cantidad ?? 0);
                }

                // Suma a ausencias totales que afectan el pago de nómina
                $resumen['dias_ausencia_total'] += (int) $diasEnPeriodo;

                // Solo suma a ausencias prestacionales si es SLN
                if ($codigo === 'SLN') {
                    $resumen['dias_ausencia_prestacional'] += (int) $diasEnPeriodo;
                }

                // SLN: solo reduce días trabajados, sin devengo monetario.
                if ($codigo === 'SLN') {
                    continue;
                }

                // IGE: empleador paga SOLO los primeros 2 días al 66.67%.
                // Los días restantes son responsabilidad de la EPS (no generan devengo aquí).
                if ($codigo === 'IGE') {
                    $valorDia = $valorHora * 8;
                    $diasPagadosIge = min(2, $diasEnPeriodo);
                    $resumen['otros_devengos'] += round($valorDia * $diasPagadosIge * 0.6667, 2);
                    continue;
                }

                // IRL: la empresa cubre solo 1 día. El resto lo cubre la ARL.
                // Además, solo aplica en el periodo de registro (no en periodos futuros).
                if ($codigo === 'IRL') {
                    $valorDia = $valorHora * 8;
                    $diasPagadosIrl = min(1, $diasEnPeriodo);
                    $resumen['otros_devengos'] += round($valorDia * $diasPagadosIrl, 2);
                    continue;
                }

                // LMAT / LPAT: paga proporcional dentro del periodo y no suma salario base
                if (in_array($codigo, ['LMAT', 'LPAT'], true)) {
                    $valorDia = $valorHora * 8;
                    $valorLicencia = round($valorDia * $diasEnPeriodo, 2);
                    $resumen['dias_licencia_maternidad'] += (int) $diasEnPeriodo;
                    $resumen['valor_licencia_maternidad'] += $valorLicencia;
                    // NO procesar en el ciclo general abajo - es especial
                    continue;
                }
            }

            // NO procesar LMAT/LPAT en el ciclo general de novedades
            // (ya fueron procesadas arriba en bloques especiales)
            if (in_array($codigo, ['LMAT', 'LPAT'], true)) {
                continue;
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

    /**
     * Procesar rollover de licencias de maternidad/paternidad del período anterior.
     * Busca novedades de LMAT/LPAT con dias_restantes_rollover y las continúa en este período.
     */
    private function procesarRollovertMaternidad(
        int $idContrato,
        int $idPeriodo,
        PeriodoLiquidacion $periodo,
        float $valorHora,
        array &$resumen
    ): void {
        $fechaInicioPeriodo = \Carbon\Carbon::parse($periodo->fecha_inicio);
        $fechaFinPeriodo = \Carbon\Carbon::parse($periodo->fecha_fin);

        // Buscar períodos anteriores para este empleado
        $periodosAnteriores = PeriodoLiquidacion::where('id_empresa', $periodo->id_empresa)
            ->where('fecha_fin', '<', $periodo->fecha_inicio)
            ->orderByDesc('fecha_fin')
            ->get()
            ->pluck('id_periodo')
            ->toArray();

        if (empty($periodosAnteriores)) {
            return;
        }

        // Buscar novedades LMAT/LPAT con rollover en períodos anteriores
        $novedadesRollover = DB::table('novedad as n')
            ->join('salario as s', 's.id_salario', '=', 'n.id_salario')
            ->where('s.id_contrato', $idContrato)
            ->whereIn('s.id_periodo', $periodosAnteriores)
            ->whereIn('n.tipo_novedad_codigo', ['LMAT', 'LPAT'])
            ->whereNotNull('n.dias_restantes_rollover')
            ->where('n.dias_restantes_rollover', '>', 0)
            ->select([
                'n.id_novedad',
                'n.dias_restantes_rollover',
                'n.tipo_novedad_codigo',
                'n.tipo_novedad_nombre',
                'n.fecha_fin',
            ])
            ->orderByDesc('s.id_periodo')
            ->first();

        if (!$novedadesRollover) {
            return;
        }

        // Procesar el rollover
        $diasRestantes = (int) ($novedadesRollover->dias_restantes_rollover ?? 0);
        if ($diasRestantes <= 0) {
            return;
        }

        // Los días en este período se limitan a 30 (o menos si es mes parcial)
        $diasDispuestos = min($diasRestantes, 30);

        // Calcular valor de la licencia continua
        $valorDia = $valorHora * 8;
        $valorLicencia = round($valorDia * $diasDispuestos, 2);

        // Agregar al resumen
        $resumen['dias_licencia_maternidad'] += (int) $diasDispuestos;
        $resumen['valor_licencia_maternidad'] += $valorLicencia;
        $resumen['dias_ausencia_total'] += (int) $diasDispuestos;

        \Illuminate\Support\Facades\Log::info(
            "ROLLOVER MATERNIDAD: Contrato {$idContrato}, Período {$idPeriodo}: "
            . "{$diasDispuestos} días ({$novedadesRollover->tipo_novedad_codigo}) = ${$valorLicencia}"
        );
    }
}