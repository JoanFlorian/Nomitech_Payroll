<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Nómina</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
            padding: 8px;
            background: #f8fafc;
        }

        .report-shell {
            background: #ffffff;
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            overflow: hidden;
        }

        .header {
            border-bottom: 2px solid #dbe3ef;
            padding: 14px 16px;
            background: linear-gradient(135deg, #eff6ff, #f0fdfa);
        }

        .title-row {
            width: 100%;
            border-collapse: collapse;
        }

        .title-row td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 11px;
            color: #475569;
        }

        .meta-wrap {
            margin-top: 8px;
            font-size: 10px;
            color: #475569;
        }

        .summary {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 10px 8px;
        }

        .summary td {
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            background: #f8fafc;
            padding: 8px;
            width: 25%;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .content {
            padding: 0 8px 10px 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #dbe3ef;
            padding: 5px 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #0f172a;
            color: #f8fafc;
            font-weight: 700;
            font-size: 9px;
        }

        .report-table {
            table-layout: fixed;
        }

        .report-table td {
            word-break: break-word;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #047857;
            font-weight: 700;
        }

        .text-danger {
            color: #b91c1c;
            font-weight: 700;
        }

        .text-strong {
            font-weight: 700;
        }

        .row-alt {
            background: #f8fafc;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 14px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @php
        $salarios = $salarios ?? collect();
        $salariosPorPagina = 8;
        $bloques = $salarios->chunk($salariosPorPagina);
    @endphp

    @if ($salarios->isEmpty())
        <div class="report-shell">
            <div class="header">
                <table class="title-row">
                    <tr>
                        <td>
                            <div class="title">Reporte de Nómina</div>
                            <div class="subtitle">Consolidado detallado de liquidaciones del periodo consultado</div>
                        </td>
                        <td class="text-right">
                            Generado: {{ $fechaGeneracion ?? now()->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                </table>
                <div class="meta-wrap">
                    Búsqueda: {{ $busqueda ?? 'Sin filtro' }} | Periodo: {{ $periodo ?? 'Sin filtro' }}
                </div>
            </div>

            <div class="content">
                <table class="report-table">
                    <colgroup>
                        <col style="width: 11%;">
                        <col style="width: 17%;">
                        <col style="width: 6%;">
                        <col style="width: 5%;">
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 9%;">
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 12%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Empleado</th>
                            <th class="text-center">Fecha pago</th>
                            <th class="text-center">Dias</th>
                            <th class="text-right">Pago por dias</th>
                            <th class="text-right">Salario inicial</th>
                            <th class="text-right">Novedades</th>
                            <th class="text-right">Devengos</th>
                            <th class="text-right">Deducciones</th>
                            <th class="text-right">Salario neto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="empty">No hay registros de nómina para exportar con los filtros seleccionados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        @foreach ($bloques as $paginaIndex => $bloque)
            <div class="report-shell">
                <div class="header">
                    <table class="title-row">
                        <tr>
                            <td>
                                <div class="title">Reporte de Nómina</div>
                                <div class="subtitle">Consolidado detallado de liquidaciones del periodo consultado</div>
                            </td>
                            <td class="text-right">
                                Generado: {{ $fechaGeneracion ?? now()->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    </table>
                    <div class="meta-wrap">
                        Búsqueda: {{ $busqueda ?? 'Sin filtro' }} | Periodo: {{ $periodo ?? 'Sin filtro' }} | Página: {{ $paginaIndex + 1 }}
                    </div>
                </div>

                <div class="content">
                    <table class="report-table">
                        <colgroup>
                            <col style="width: 11%;">
                            <col style="width: 17%;">
                            <col style="width: 6%;">
                            <col style="width: 5%;">
                            <col style="width: 10%;">
                            <col style="width: 10%;">
                            <col style="width: 9%;">
                            <col style="width: 10%;">
                            <col style="width: 10%;">
                            <col style="width: 12%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Empleado</th>
                                <th class="text-center">Fecha pago</th>
                                <th class="text-center">Dias</th>
                                <th class="text-right">Pago por dias</th>
                                <th class="text-right">Salario inicial</th>
                                <th class="text-right">Novedades</th>
                                <th class="text-right">Devengos</th>
                                <th class="text-right">Deducciones</th>
                                <th class="text-right">Salario neto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bloque as $idx => $salario)
                                @php
                                    $contrato = $salario->contrato;
                                    $usuario = $contrato?->usuario;
                                    $diasTrabajados = max(0, min(30, (int) ($salario->dias_a_trabajar ?? 0)));
                                    $salarioBase = (float) ($contrato?->salario_base ?? 0);
                                    $valorDia = $salarioBase / 30;
                                    $pagoPorDias = $valorDia * $diasTrabajados;
                                    $novedades = (float) ($salario->total_novedades ?? 0);
                                @endphp
                                <tr class="{{ $idx % 2 === 1 ? 'row-alt' : '' }}">
                                    <td>{{ $usuario?->doc ?? '' }}</td>
                                    <td class="text-strong">{{ mb_strtoupper((string) ($usuario?->nombre_completo ?? ''), 'UTF-8') }}</td>
                                    <td class="text-center">{{ $salario->fecha_pago ? \Carbon\Carbon::parse($salario->fecha_pago)->format('Y-m-d') : '' }}</td>
                                    <td class="text-center text-strong">{{ $diasTrabajados }}</td>
                                    <td class="text-right">${{ number_format($pagoPorDias, 0, ',', '.') }}</td>
                                    <td class="text-right">${{ number_format($salarioBase, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $novedades >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $novedades >= 0 ? '+' : '-' }}${{ number_format(abs($novedades), 0, ',', '.') }}
                                    </td>
                                    <td class="text-right text-success">${{ number_format((float) ($salario->total_devengado - $salario->total_novedades_devengado), 0, ',', '.') }}</td>
                                    <td class="text-right text-danger">${{ number_format((float) $salario->getRawOriginal('total_deducciones'), 0, ',', '.') }}</td>
                                    <td class="text-right text-strong">${{ number_format((float) $salario->neto_pagar, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if (!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endif
</body>

</html>
