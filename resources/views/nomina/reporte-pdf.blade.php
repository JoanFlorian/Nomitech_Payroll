<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Nómina</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        .header {
            border-bottom: 2px solid #111827;
            margin-bottom: 14px;
            padding-bottom: 8px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Reporte de Nómina</div>
        <div class="meta">Generado: {{ $fechaGeneracion }}</div>
        <div class="meta">Búsqueda: {{ $busqueda }} | Periodo: {{ $periodo }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Documento</th>
                <th>Empleado</th>
                <th>Fecha pago</th>
                <th class="text-right">Salario inicial</th>
                <th class="text-right">Devengos</th>
                <th class="text-right">Deducciones</th>
                <th class="text-right">Salario neto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salarios as $salario)
                <tr>
                    <td>{{ $salario->contrato->usuario->doc ?? '' }}</td>
                    <td>{{ $salario->contrato->usuario->nombre_completo ?? '' }}</td>
                    <td>{{ $salario->fecha_pago ? \Carbon\Carbon::parse($salario->fecha_pago)->format('Y-m-d') : '' }}</td>
                    <td class="text-right">${{ number_format((float) ($salario->contrato->salario_base ?? 0), 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format((float) $salario->total_devengos, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format((float) $salario->total_deducciones, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format((float) $salario->salario_neto, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">No hay registros de nómina para exportar con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
