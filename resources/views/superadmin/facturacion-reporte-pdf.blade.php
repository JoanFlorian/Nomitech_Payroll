<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Facturación por Empresas</title>
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
        <div class="title">Reporte de Facturación por Empresas</div>
        <div class="meta">Generado: {{ $fechaGeneracion }}</div>
        <div class="meta">Estado: {{ $estadoSeleccionado }} | Método: {{ $metodoSeleccionado }} | Búsqueda: {{ $busqueda }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Empresa</th>
                <th>NIT</th>
                <th>Referencia</th>
                <th>Fecha</th>
                <th>Plan</th>
                <th>Método</th>
                <th>Estado</th>
                <th>Vigencia</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $pago)
                @php
                    $licencia = $pago->licencia;
                    $empresa = $pago->empresa ?? ($licencia ? $licencia->empresa : null);
                    $plan = $pago->plan ?? ($licencia ? $licencia->plan : null);

                    $fechaFin = $licencia?->fecha_fin ? \Carbon\Carbon::parse($licencia->fecha_fin) : null;
                    $diasRestantes = $fechaFin ? \Carbon\Carbon::now()->diffInDays($fechaFin, false) : null;
                    $vigencia = is_null($diasRestantes) ? 'Sin vigencia' : ($diasRestantes < 0 ? 'Vencida' : abs((int) floor($diasRestantes)) . ' días');

                    $estadoTexto = match ($pago->estado_pago) {
                        'paid' => 'Pagado',
                        'pending' => 'Pendiente',
                        'failed' => 'Fallido',
                        default => $pago->estado_pago,
                    };

                    $fechaPago = $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : $pago->created_at->format('d/m/Y');
                @endphp
                <tr>
                    <td>{{ $empresa->razon_social ?? 'Sin especificar' }}</td>
                    <td>{{ $empresa->nit ?? 'No especificado' }}</td>
                    <td>{{ $pago->referencia ?? 'FAC-' . $pago->id }}</td>
                    <td>{{ $fechaPago }}</td>
                    <td>{{ $plan->nombre ?? 'Plan de suscripción' }}</td>
                    <td>{{ $pago->proveedor_pago ?? '—' }}</td>
                    <td>{{ $estadoTexto }}</td>
                    <td>{{ $vigencia }}</td>
                    <td class="text-right">${{ number_format((float) $pago->valor, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty">No hay datos para exportar con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
