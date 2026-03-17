<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desprendible de Nómina</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1565C0;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1565C0;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .info-box {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            color: #555;
        }
        .info-value {
            display: table-cell;
            width: 70%;
            color: #333;
        }
        .section-title {
            background-color: #1565C0;
            color: white;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #e3f2fd;
            color: #1565C0;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #1565C0;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #e8f5e9;
            font-weight: bold;
        }
        .total-row td {
            padding: 12px 10px;
            font-size: 14px;
            color: #2e7d32;
        }
        .deduction-total {
            background-color: #fff3e0;
            color: #e65100;
        }
        .final-total {
            background-color: #1565C0;
            color: white;
            font-size: 16px;
            padding: 15px 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>DESPRENDIBLE DE NÓMINA</h1>
        <p>Nomitech - Sistema de Gestión de Nómina</p>
    </div>

    <!-- Información del Empleado -->
    <div class="info-box">
        <h3 style="color: #1565C0; margin-bottom: 10px;">INFORMACIÓN DEL EMPLEADO</h3>
        <div class="info-row">
            <div class="info-label">Empleado:</div>
            <div class="info-value">{{ $desprendible->contrato->usuario->nombre_completo }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Documento:</div>
            <div class="info-value">{{ $desprendible->contrato->usuario->numero_documento }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Periodo:</div>
            <div class="info-value">{{ $desprendible->periodo->nombre ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de Pago:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($desprendible->created_at)->format('d/m/Y') }}</div>
        </div>
    </div>

    <!-- Devengos -->
    <div class="section-title">DEVENGOS</div>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="text-right" style="width: 30%;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devengos as $concepto => $valor)
                @if($valor > 0)
                    <tr>
                        <td>{{ $concepto }}</td>
                        <td class="text-right">${{ number_format($valor, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL DEVENGOS</strong></td>
                <td class="text-right"><strong>${{ number_format($desprendible->total_devengos, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Deducciones -->
    <div class="section-title">DEDUCCIONES</div>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="text-right" style="width: 30%;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deducciones as $concepto => $valor)
                @if($valor > 0)
                    <tr>
                        <td>{{ $concepto }}</td>
                        <td class="text-right">${{ number_format($valor, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row deduction-total">
                <td><strong>TOTAL DEDUCCIONES</strong></td>
                <td class="text-right"><strong>${{ number_format($desprendible->total_deducciones, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Total Neto -->
    <table>
        <tr class="final-total">
            <td><strong>NETO A PAGAR</strong></td>
            <td class="text-right" style="width: 30%;"><strong>${{ number_format($desprendible->salario_neto, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento es generado automáticamente por el sistema Nomitech</p>
        <p>Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
