<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Prestación - {{ $movement->reference }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            width: 100%;
            background-color: #1565C0;
            color: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-ref {
            font-size: 11px;
            opacity: 0.9;
        }
        .header-company {
            text-align: right;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 20px;
        }
        .info-grid {
            width: 100%;
            background-color: #f9f9f9;
            border: 1px solid #eeeeee;
            padding: 15px;
            border-radius: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        .concepts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .concepts-table th {
            text-align: left;
            border-bottom: 2px solid #eeeeee;
            padding: 8px 0;
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .concepts-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .text-right {
            text-align: right;
        }
        .amount-cell {
            font-size: 14px;
            font-weight: bold;
        }
        .total-row {
            border-top: 2px solid #333;
        }
        .total-label {
            text-align: right;
            padding: 15px 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .total-value {
            padding: 15px 0;
            font-size: 20px;
            font-weight: bold;
            text-align: right;
        }
        .signatures {
            margin-top: 60px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #999;
            padding-top: 8px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #aaa;
        }
    </style>
</head>
<body>
    @php
        $empresa = collect([
            $movement->empresa->razon_social ?? '',
            $movement->empresa->sigla ?? ''
        ])->filter()->implode(' - ');

        $empleado = collect([
            $movement->usuario->primer_nombre ?? '',
            $movement->usuario->otros_nombres ?? '',
            $movement->usuario->primer_apellido ?? '',
            $movement->usuario->segundo_apellido ?? ''
        ])->filter()->implode(' ');

        $isWithdrawal = $movement->movement_type === 'withdrawal';
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">Comprobante de Prestación</div>
                    <div class="header-ref">Ref: #{{ str_pad($movement->id, 8, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td class="header-company">
                    <div class="company-name">{{ $empresa ?: 'Nomitech Payroll' }}</div>
                    <div style="font-size: 11px;">NIT: {{ $movement->empresa->nit ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="container">
        <div class="section">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <div class="info-label">Fecha de Emisión</div>
                        <div class="info-value">{{ $movement->created_at->format('d/m/Y h:i A') }}</div>
                    </td>
                    <td style="text-align: right;">
                        <div class="info-label">Estado</div>
                        <div class="info-value">{{ strtoupper($movement->status ?? 'APLICADO') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="info-grid">
                <table class="info-table">
                    <tr>
                        <td style="width: 50%;">
                            <div class="info-label">Beneficiario / Empleado</div>
                            <div class="info-value">{{ $empleado }}</div>
                            <div style="font-size: 10px; color: #666;">CC: {{ $movement->employee_id }}</div>
                        </td>
                        <td>
                            <div class="info-label">Destino del Pago</div>
                            <div class="info-value">
                                @if($movement->destination === 'fund')
                                    Fondo de Cesantías ({{ $movement->usuario->fondo_cesantias ?? 'Por Asignar' }})
                                @else
                                    Pago Directo al Empleado
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <table class="concepts-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Concepto / Referencia</th>
                        <th>Tipo</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $movement->reference }}</td>
                        <td>{{ App\Models\BenefitLedger::benefitTypeLabel($movement->benefit_type) }}</td>
                        <td class="text-right amount-cell">
                            ${{ number_format(abs($movement->amount), 2, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="total-label">Total Aprobado:</td>
                        <td class="total-value">${{ number_format(abs($movement->amount), 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="signatures">
            <table style="width: 100%;">
                <tr>
                    <td class="signature-box">
                        <div style="font-weight: bold;">Firma Empleador</div>
                        <div style="font-size: 10px; color: #666;">{{ $empresa ?: 'Nomitech Payroll' }}</div>
                    </td>
                    <td style="width: 10%;"></td>
                    <td class="signature-box">
                        <div style="font-weight: bold;">Firma Empleado / Beneficiario</div>
                        <div style="font-size: 10px; color: #666;">CC: {{ $movement->employee_id }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Generado por Nomitech Payroll • Comprobante válido para requerimientos del empleado o fondo.<br>
            Impreso el: {{ date('d/m/Y h:i A') }}
        </div>
    </div>
</body>
</html>
