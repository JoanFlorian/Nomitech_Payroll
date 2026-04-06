<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Desprendible de Nómina</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #1565C0; padding-bottom: 12px; }
        .header h1 { color: #1565C0; font-size: 20px; margin-bottom: 4px; }
        .header p { color: #666; font-size: 12px; }
        .section-title {
            background-color: #1565C0; color: white;
            padding: 7px 10px; font-size: 12px; font-weight: bold;
            margin-top: 18px; margin-bottom: 8px;
        }
        .info-box { background-color: #f5f5f5; padding: 12px; border-radius: 4px; margin-bottom: 16px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; width: 50%; padding: 4px 6px; vertical-align: top; }
        .info-label { font-weight: bold; color: #555; font-size: 10px; text-transform: uppercase; letter-spacing: 0.03em; }
        .info-value { color: #222; font-size: 11px; }
        .ibc-box {
            background-color: #e3f2fd; border: 1px solid #90caf9;
            padding: 8px 12px; border-radius: 4px;
            margin-bottom: 10px; font-size: 11px; color: #1565C0;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table th {
            background-color: #e3f2fd; color: #1565C0;
            padding: 8px 10px; text-align: left; font-weight: bold;
            border-bottom: 2px solid #1565C0; font-size: 10px; text-transform: uppercase;
        }
        table td { padding: 7px 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .subdetalle-row td { background-color: #fafafa; padding: 4px 10px 4px 20px; font-size: 10px; color: #666; }
        .total-row td { background-color: #e8f5e9; font-weight: bold; font-size: 12px; color: #2e7d32; padding: 10px; }
        .deduction-total td { background-color: #fff3e0; color: #e65100; }
        .novedad-row td { background-color: #fff8e1; }
        .summary-box { display: table; width: 100%; margin-top: 20px; background-color: #1565C0; color: white; border-radius: 4px; }
        .summary-cell { display: table-cell; width: 33.3%; text-align: center; padding: 14px 8px; }
        .summary-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #bbdefb; margin-bottom: 4px; }
        .summary-value { font-size: 18px; font-weight: bold; }
        .summary-neto { background-color: rgba(255,255,255,0.2); border-radius: 4px; }
        .ss-box { display: table; width: 100%; margin-bottom: 16px; }
        .ss-cell { display: table-cell; width: 33.3%; padding: 8px; }
        .ss-inner { background-color: #ede7f6; border: 1px solid #ce93d8; border-radius: 4px; padding: 8px; text-align: center; }
        .badge-dias { background-color: #e3f2fd; color: #1565C0; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .footer { margin-top: 32px; text-align: center; color: #aaa; font-size: 9px; border-top: 1px solid #ddd; padding-top: 12px; }
    </style>
</head>
<body>
@php
    $contrato      = $desprendible->contrato;
    $usuario       = $contrato->usuario;
    $periodo       = $desprendible->periodo;
    $diasTrabajados = (int) ($desprendible->dias_trabajados ?? $desprendible->dias_a_trabajar ?? 30);
    $diasPeriodo   = 30;
    $diasNoTrabajados = max(0, $diasPeriodo - $diasTrabajados);
    $salarioBase   = (float) ($desprendible->salario_base ?? $contrato->salario_base ?? 0);
    $ibc           = $salarioBase
                     + (float) ($desprendible->valor_horas_extras_recargos ?? $desprendible->horas_extra ?? 0)
                     + (float) ($desprendible->bonificaciones ?? 0)
                     + (float) ($desprendible->comisiones ?? 0)
                     + (float) ($desprendible->otros_devengos ?? 0);
    $novedadesPeriodo = $desprendible->novedades;
    $horasExtrasTotalValor = (float) ($desprendible->valor_horas_extras_recargos ?? $desprendible->horas_extra ?? 0);
    $salarioBaseProporcional = ($salarioBase / 30) * $diasTrabajados;
@endphp

<!-- Header -->
<div class="header">
    <h1>DESPRENDIBLE DE NÓMINA</h1>
    <p>Nomitech — Sistema de Gestión de Nómina</p>
</div>

<!-- 1. Información del empleado -->
<div class="section-title">INFORMACIÓN DEL EMPLEADO</div>
<div class="info-box">
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Empleado</div>
                <div class="info-value">{{ $usuario->nombre_completo ?? 'N/A' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Documento</div>
                <div class="info-value">{{ $usuario->numero_documento ?? $usuario->doc ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Periodo</div>
                <div class="info-value">{{ $periodo->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Fecha de pago</div>
                <div class="info-value">
                    {{ \Carbon\Carbon::parse($desprendible->fecha_pago ?? $desprendible->created_at)->format('d/m/Y') }}
                </div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Tipo de contrato</div>
                <div class="info-value">{{ $contrato->tipoContrato->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Salario base</div>
                <div class="info-value">${{ number_format($salarioBase, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Días trabajados</div>
                <div class="info-value">
                    {{ $diasTrabajados }} de {{ $diasPeriodo }}
                    @if($diasNoTrabajados > 0)
                        &nbsp;&nbsp;<span class="badge-dias">{{ $diasNoTrabajados }} no trabajados</span>
                    @endif
                </div>
            </div>
            @if($contrato->metodoPago ?? null)
            <div class="info-cell">
                <div class="info-label">Método de pago</div>
                <div class="info-value">{{ $contrato->metodoPago->nombre ?? 'N/A' }}</div>
            </div>
            @endif
        </div>
        @if(!empty($contrato->tipo_cuenta) && !empty($contrato->numero_cuenta))
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Cuenta bancaria</div>
                <div class="info-value">{{ ucfirst(strtolower($contrato->tipo_cuenta)) }} — {{ $contrato->numero_cuenta }}</div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- 2. Novedades del periodo -->
@if($novedadesPeriodo->isNotEmpty())
<div class="section-title" style="background-color:#e65100;">NOVEDADES DEL PERIODO</div>
<table>
    <thead>
        <tr>
            <th>Tipo de novedad</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th class="text-right">Días</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($novedadesPeriodo as $nov)
        <tr class="novedad-row">
            <td>{{ $nov->tipo_novedad_nombre ?? $nov->tipoNovedad?->nombre ?? 'Novedad' }}</td>
            <td>{{ optional($nov->fecha_inicio ?? $nov->fecha)->format('d/m/Y') ?? '—' }}</td>
            <td>{{ optional($nov->fecha_fin)->format('d/m/Y') ?? '—' }}</td>
            <td class="text-right">{{ $nov->dias > 0 ? $nov->dias : '—' }}</td>
            <td style="font-size:10px;">{{ $nov->observaciones ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!-- 3. Devengos -->
<div class="section-title">DEVENGOS</div>
<table>
    <thead>
        <tr>
            <th>Concepto</th>
            <th class="text-right" style="width:26%;">Valor</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Salario base{{ $diasTrabajados < $diasPeriodo ? " ($diasTrabajados días)" : '' }}</td>
            <td class="text-right">${{ number_format($salarioBaseProporcional, 0, ',', '.') }}</td>
        </tr>

        @if((float)($desprendible->auxilio_transporte ?? 0) > 0)
        <tr>
            <td>Auxilio de transporte</td>
            <td class="text-right">${{ number_format((float)$desprendible->auxilio_transporte, 0, ',', '.') }}</td>
        </tr>
        @endif

        @if($horasExtrasTotalValor > 0)
        <tr>
            <td>Horas extras y recargos</td>
            <td class="text-right">${{ number_format($horasExtrasTotalValor, 0, ',', '.') }}</td>
        </tr>
        @if($horasExtras->isNotEmpty())
            @foreach($horasExtras as $hx)
            @php
                $cantidadHX = (float) $hx->cantidad;
                $pagoHX     = (float) $hx->pago;
            @endphp
            <tr class="subdetalle-row">
                <td>↳ {{ $hx->tipoHoraRecargo->nombre ?? 'Tipo' }} — {{ number_format($cantidadHX, 1, ',', '.') }} h</td>
                <td class="text-right">${{ number_format($pagoHX, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        @endif
        @endif

        @if((float)($desprendible->bonificaciones ?? 0) > 0)
        <tr>
            <td>Bonificaciones</td>
            <td class="text-right">${{ number_format((float)$desprendible->bonificaciones, 0, ',', '.') }}</td>
        </tr>
        @endif

        @if((float)($desprendible->comisiones ?? 0) > 0)
        <tr>
            <td>Comisiones</td>
            <td class="text-right">${{ number_format((float)$desprendible->comisiones, 0, ',', '.') }}</td>
        </tr>
        @endif

        @if((float)($desprendible->otros_devengos ?? 0) > 0)
        <tr>
            <td>Otros devengos</td>
            <td class="text-right">${{ number_format((float)$desprendible->otros_devengos, 0, ',', '.') }}</td>
        </tr>
        @endif

        @foreach($desprendible->novedades as $nov)
            @if((float)($nov->pago ?? 0) > 0)
            <tr class="novedad-row">
                <td>{{ $nov->tipo_novedad_nombre ?? $nov->tipoNovedad?->nombre ?? 'Novedad' }}</td>
                <td class="text-right">${{ number_format((float)$nov->pago, 0, ',', '.') }}</td>
            </tr>
            @endif
        @endforeach

        <tr class="total-row">
            <td><strong>TOTAL DEVENGOS</strong></td>
            <td class="text-right"><strong>${{ number_format((float)$desprendible->total_devengos, 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- 4. Deducciones -->
<div class="section-title">DEDUCCIONES</div>
<div class="ibc-box"><strong>Base de cotización (IBC):</strong> ${{ number_format($ibc, 0, ',', '.') }}</div>
<table>
    <thead>
        <tr>
            <th>Concepto</th>
            <th class="text-right" style="width:26%;">Valor</th>
        </tr>
    </thead>
    <tbody>
        @if((float)($desprendible->eps ?? 0) > 0)
        <tr>
            <td>Salud (EPS — 4%)</td>
            <td class="text-right">${{ number_format((float)$desprendible->eps, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if((float)($desprendible->afp ?? 0) > 0)
        <tr>
            <td>Pensión AFP (4%)</td>
            <td class="text-right">${{ number_format((float)$desprendible->afp, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if((float)($desprendible->aporte_fp ?? 0) > 0)
        <tr>
            <td>Fondo de solidaridad pensional</td>
            <td class="text-right">${{ number_format((float)$desprendible->aporte_fp, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if((float)($desprendible->retencion_fuente ?? 0) > 0)
        <tr>
            <td>Retención en la fuente</td>
            <td class="text-right">${{ number_format((float)$desprendible->retencion_fuente, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if((float)($desprendible->embargo_fiscal ?? 0) > 0)
        <tr>
            <td>Embargo fiscal</td>
            <td class="text-right">${{ number_format((float)$desprendible->embargo_fiscal, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if((float)($desprendible->pension_voluntaria ?? 0) > 0)
        <tr>
            <td>Pensión voluntaria</td>
            <td class="text-right">${{ number_format((float)$desprendible->pension_voluntaria, 0, ',', '.') }}</td>
        </tr>
        @endif
        @foreach($desprendible->novedades as $nov)
            @if((float)($nov->pago ?? 0) < 0)
            <tr class="novedad-row">
                <td>{{ $nov->tipo_novedad_nombre ?? $nov->tipoNovedad?->nombre ?? 'Novedad' }}</td>
                <td class="text-right">${{ number_format(abs((float)$nov->pago), 0, ',', '.') }}</td>
            </tr>
            @endif
        @endforeach
        <tr class="total-row deduction-total">
            <td><strong>TOTAL DEDUCCIONES</strong></td>
            <td class="text-right"><strong>${{ number_format((float)$desprendible->total_deducciones, 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- 5. Seguridad social (solo visual) -->
@if((float)($desprendible->eps ?? 0) > 0 || (float)($desprendible->afp ?? 0) > 0)
<div class="section-title" style="background-color:#4a148c;">APORTES SEGURIDAD SOCIAL (EMPLEADO)</div>
<div class="ss-box">
    <div class="ss-cell">
        <div class="ss-inner" style="background:#e3f2fd;border-color:#90caf9;">
            <div class="info-label" style="color:#1565C0;">Base IBC</div>
            <div style="font-size:13px;font-weight:bold;color:#1565C0;">${{ number_format($ibc, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="ss-cell">
        <div class="ss-inner" style="background:#e8f5e9;border-color:#a5d6a7;">
            <div class="info-label" style="color:#2e7d32;">Salud (4%)</div>
            <div style="font-size:13px;font-weight:bold;color:#2e7d32;">${{ number_format((float)($desprendible->eps ?? 0), 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="ss-cell">
        <div class="ss-inner">
            <div class="info-label" style="color:#6a1b9a;">Pensión (4%)</div>
            <div style="font-size:13px;font-weight:bold;color:#6a1b9a;">${{ number_format((float)($desprendible->afp ?? 0), 0, ',', '.') }}</div>
        </div>
    </div>
</div>
@endif

<!-- 6. Resumen final -->
<div class="section-title">RESUMEN</div>
<div class="summary-box">
    <div class="summary-cell">
        <div class="summary-label">Total devengado</div>
        <div class="summary-value">${{ number_format((float)$desprendible->total_devengos, 0, ',', '.') }}</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Total deducciones</div>
        <div class="summary-value">${{ number_format((float)$desprendible->total_deducciones, 0, ',', '.') }}</div>
    </div>
    <div class="summary-cell summary-neto">
        <div class="summary-label">Neto a pagar</div>
        <div class="summary-value" style="font-size:22px;">${{ number_format((float)$desprendible->salario_neto, 0, ',', '.') }}</div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>Este documento es generado automáticamente por el sistema Nomitech</p>
    <p>Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
</div>
</body>
</html>
