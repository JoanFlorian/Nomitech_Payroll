<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Nómina</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #172b43; }
        .header {
            border: 1px solid #dbeafe;
            background: #f4f8ff;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 14px;
        }
        .brand-table { width: 100%; border-collapse: collapse; }
        .brand-table td { border: none; vertical-align: top; }
        .logo-wrap { width: 38%; }
        .logo { height: 64px; }
        .title-wrap { width: 62%; text-align: right; }
        .title { font-size: 20px; font-weight: 700; color: #0f3d7a; margin-bottom: 5px; }
        .meta { font-size: 10px; color: #425466; line-height: 1.45; }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 14px 0 7px;
            color: #0f3d7a;
            border-left: 4px solid #1d6fd8;
            padding-left: 8px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d5e4fb; padding: 7px; text-align: left; }
        th { background: #eef5ff; color: #173f73; font-weight: 700; }
        .text-right { text-align: right; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; width: 50%; border: none; padding: 0 8px 0 0; }
        .kpi-table td { border-color: #d5e4fb; }
        .kpi-title { color: #3b5778; font-weight: 700; width: 28%; }
        .kpi-value { font-weight: 700; color: #102a43; }
        .footer {
            margin-top: 12px;
            font-size: 9px;
            color: #6b7f98;
            text-align: center;
            border-top: 1px solid #dbe7fb;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    @php
        $costoTotalNomina = (float) ($resumen->costo_total_nomina ?? 0);
        $empleadosActivos = (int) ($resumen->empleados_activos ?? 0);
        $salarioNetoPromedio = (float) ($resumen->salario_neto_promedio ?? 0);
        $aportesSeguridadSocial = (float) ($resumen->aportes_seguridad_social ?? 0);
        $totalDeducciones = (float) ($resumen->total_deducciones ?? 0);
        $logoPath = public_path('images/nomitech-logo.svg');
        $logoSrc = file_exists($logoPath) ? 'file:///' . str_replace('\\', '/', $logoPath) : null;
    @endphp

    <div class="header">
        <table class="brand-table">
            <tr>
                <td class="logo-wrap">
                    @if($logoSrc)
                        <img class="logo" src="{{ $logoSrc }}" alt="Nomitech">
                    @else
                        <div style="font-size: 22px; font-weight: 800; color: #1565c0;">NOMITECH</div>
                    @endif
                </td>
                <td class="title-wrap">
                    <div class="title">Reporte de Nomina</div>
                    <div class="meta">Generado: {{ $fechaGeneracion }}</div>
                    <div class="meta">
                        Periodo: {{ $periodoSeleccionado === 'all' ? 'Todos' : \Carbon\Carbon::createFromFormat('Y-m', $periodoSeleccionado)->locale('es')->translatedFormat('F Y') }}
                    </div>
                    <div class="meta">Tipo de contrato: {{ $tipoContratoNombre }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Resumen de Nomina</div>
    <table class="kpi-table">
        <tbody>
            <tr>
                <td class="kpi-title">Costo total de nomina</td>
                <td class="text-right kpi-value">${{ number_format($costoTotalNomina, 0, ',', '.') }}</td>
                <td class="kpi-title">Empleados activos</td>
                <td class="text-right kpi-value">{{ $empleadosActivos }}</td>
            </tr>
            <tr>
                <td class="kpi-title">Salario neto promedio</td>
                <td class="text-right kpi-value">${{ number_format($salarioNetoPromedio, 0, ',', '.') }}</td>
                <td class="kpi-title">Aportes seguridad social</td>
                <td class="text-right kpi-value">${{ number_format($aportesSeguridadSocial, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="kpi-title">Total deducciones</td>
                <td class="text-right kpi-value">${{ number_format($totalDeducciones, 0, ',', '.') }}</td>
                <td class="kpi-title"></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="grid" style="margin-top: 14px;">
        <tr>
            <td>
                <div class="section-title" style="margin-top: 0;">Desglose de Nomina</div>
                <table>
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Salarios</td>
                            <td class="text-right">${{ number_format($desglose['salarios'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Bonificaciones</td>
                            <td class="text-right">${{ number_format($desglose['bonificaciones'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Prestaciones Sociales</td>
                            <td class="text-right">${{ number_format($desglose['prestaciones_sociales'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Provisiones</td>
                            <td class="text-right">${{ number_format($desglose['provisiones'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td>
                <div class="section-title" style="margin-top: 0;">Evolucion de Nomina</div>
                <table>
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evolucion as $item)
                            <tr>
                                <td>{{ strtoupper($item['label']) }}</td>
                                <td class="text-right">${{ number_format($item['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Sin datos para la selección actual.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado por Nomitech. Este reporte consolida los valores segun los filtros aplicados.
    </div>
</body>
</html>
