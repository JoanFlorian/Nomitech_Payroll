<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Nómina</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .header { margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .meta { font-size: 11px; color: #4b5563; }
        .section-title { font-size: 14px; font-weight: 700; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; }
        .text-right { text-align: right; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; width: 50%; border: none; padding: 0 8px 0 0; }
    </style>
</head>
<body>
    @php
        $costoTotalNomina = (float) ($resumen->costo_total_nomina ?? 0);
        $empleadosActivos = (int) ($resumen->empleados_activos ?? 0);
        $salarioNetoPromedio = (float) ($resumen->salario_neto_promedio ?? 0);
        $aportesSeguridadSocial = (float) ($resumen->aportes_seguridad_social ?? 0);
        $totalDeducciones = (float) ($resumen->total_deducciones ?? 0);
    @endphp

    <div class="header">
        <div class="title">Reporte de Nómina</div>
        <div class="meta">Generado: {{ $fechaGeneracion }}</div>
        <div class="meta">
            Periodo:
            {{ $periodoSeleccionado === 'all' ? 'Todos' : \Carbon\Carbon::createFromFormat('Y-m', $periodoSeleccionado)->locale('es')->translatedFormat('F Y') }}
        </div>
        <div class="meta">Tipo de contrato: {{ $tipoContratoNombre }}</div>
    </div>

    <div class="section-title">Resumen de Nómina</div>
    <table>
        <tbody>
            <tr>
                <th>Costo total de nómina</th>
                <td class="text-right">${{ number_format($costoTotalNomina, 0, ',', '.') }}</td>
                <th>Empleados activos</th>
                <td class="text-right">{{ $empleadosActivos }}</td>
            </tr>
            <tr>
                <th>Salario neto promedio</th>
                <td class="text-right">${{ number_format($salarioNetoPromedio, 0, ',', '.') }}</td>
                <th>Aportes seguridad social</th>
                <td class="text-right">${{ number_format($aportesSeguridadSocial, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total deducciones</th>
                <td class="text-right">${{ number_format($totalDeducciones, 0, ',', '.') }}</td>
                <th></th>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="grid" style="margin-top: 14px;">
        <tr>
            <td>
                <div class="section-title" style="margin-top: 0;">Desglose de Nómina</div>
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
                <div class="section-title" style="margin-top: 0;">Evolución de Nómina</div>
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
</body>
</html>
