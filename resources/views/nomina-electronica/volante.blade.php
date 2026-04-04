<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Volante de Pago - {{ $empleado->nombre_completo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #1565C0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1565C0;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            color: #666;
        }
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-section td {
            padding: 4px;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }
        .concepts-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .concepts-table {
            width: 100%;
            border-collapse: collapse;
        }
        .concepts-table th {
            background-color: #f5f5f5;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        .concepts-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .totals-section {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            width: 300px;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px;
        }
        .total-row {
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #1565C0;
        }
        .footer {
            margin-top: 100px;
            width: 100%;
            font-size: 10px;
            color: #888;
            text-align: center;
        }
        .signature-box {
            margin-top: 50px;
            width: 250px;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 5px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="company-name">{{ mb_strtoupper($empresa->razon_social) }}</div>
                        <div style="font-size: 10px; color: #666;">
                            NIT: {{ $empresa->nit }}-{{ $empresa->nit_dv }}<br>
                            {{ $empresa->direccion }} - {{ $empresa->ciudad->nombre ?? '' }}<br>
                            Tel: {{ $empresa->telefono }}
                        </div>
                    </td>
                    <td class="report-title">
                        VOLANTE DE PAGO DE NÓMINA<br>
                        <span style="font-size: 11px; font-weight: normal;">
                            Periodo: {{ $periodo->fecha_inicio->format('d/m/Y') }} al {{ $periodo->fecha_fin->format('d/m/Y') }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="info-section">
            <table>
                <tr>
                    <td class="label">Empleado:</td>
                    <td>{{ mb_strtoupper($empleado->nombre_completo) }}</td>
                    <td class="label">Identificación:</td>
                    <td>{{ $empleado->doc }}</td>
                </tr>
                <tr>
                    <td class="label">Cargo:</td>
                    <td>{{ $salario->contrato->cargo ?? 'N/A' }}</td>
                    <td class="label">Sueldo Básico:</td>
                    <td>${{ number_format($salario->contrato->salario_base, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Días Trabajados:</td>
                    <td>{{ $salario->dias_trabajados }}</td>
                    <td class="label">Fecha de Pago:</td>
                    <td>{{ $salario->fecha_pago ? \Carbon\Carbon::parse($salario->fecha_pago)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="concepts-section">
            <table class="concepts-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">CONCEPTO</th>
                        <th style="width: 25%;" class="text-right">DEVENGADOS</th>
                        <th style="width: 25%;" class="text-right">DEDUCCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Earnings First --}}
                    @foreach($devengos as $dev)
                        <tr>
                            <td>{{ $dev['concepto'] }}</td>
                            <td class="text-right">${{ number_format($dev['valor'], 0, ',', '.') }}</td>
                            <td class="text-right"></td>
                        </tr>
                    @endforeach

                    {{-- Deductions Second --}}
                    @foreach($deducciones as $ded)
                        <tr>
                            <td style="color: #444;">{{ $ded['concepto'] }}</td>
                            <td class="text-right"></td>
                            <td class="text-right">${{ number_format($ded['valor'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals-section clearfix">
            <table class="totals-table">
                <tr>
                    <td>Total Devengados:</td>
                    <td class="text-right">${{ number_format($totalDevengos, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Deducciones:</td>
                    <td class="text-right">${{ number_format($totalDeducciones, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>NETO A PAGAR:</td>
                    <td class="text-right">${{ number_format($netoPagar, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="signature-box">
            Firma del Empleado<br>
            C.C. {{ $empleado->doc }}
        </div>

        <div class="footer">
            Este documento es un comprobante de pago de nómina generado por Nomitech.<br>
            Fecha de impresión: {{ $fechaGeneracion }}
        </div>
    </div>
</body>
</html>
