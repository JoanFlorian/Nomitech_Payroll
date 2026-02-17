<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }

        .title {
            font-size: 20px;
            margin-top: 10px;
        }

        .meta-container {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .meta-box {
            display: table-cell;
            width: 33%;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .meta-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }

        .meta-value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">NOMITECH</div>
        <div class="title">{{ $titulo }}</div>
        <div style="font-size: 12px; color: #6b7280;">Generado el: {{ $fechaGeneracion }}</div>
    </div>

    <div class="meta-container">
        <div class="meta-box">
            <div class="meta-label">Ingresos Totales</div>
            <div class="meta-value">${{ $totalIncome }}</div>
        </div>
        <div class="meta-box" style="border-left: none; border-right: none;">
            <div class="meta-label">Licencia más Vendida</div>
            <div class="meta-value">{{ $topLicenseName }}</div>
        </div>
        <div class="meta-box">
            <div class="meta-label">Ventas totales</div>
            <div class="meta-value">{{ count($pagos) }}</div>
        </div>
    </div>

    <h3>Desglose de Transacciones</h3>
    <table>
        <thead>
            <tr>
                <th>Referencia</th>
                <th>Fecha/Hora</th>
                <th>Concepto</th>
                <th>Método</th>
                <th>Valor</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago->referencia ?? 'FAC-' . $pago->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($pago->fecha_pago ?? $pago->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $pago->licencia->plan->nombre ?? 'Suscripción' }}</td>
                    <td>{{ ucfirst($pago->proveedor_pago) }}</td>
                    <td>${{ number_format($pago->valor, 0, ',', '.') }}</td>
                    <td><span class="status status-paid">PAGADO</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} Nomitech S.A.S. - Sistema de Facturación y Nómina Automatizada
    </div>
</body>

</html>