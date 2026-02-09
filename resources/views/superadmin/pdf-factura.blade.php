<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $numeroFactura }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        
        .header-left h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header-left p {
            color: #6b7280;
            font-size: 14px;
            margin: 3px 0;
        }
        
        .header-right {
            text-align: right;
        }
        
        .header-right .invoice-number {
            font-size: 24px;
            color: #2563eb;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header-right .invoice-date {
            color: #6b7280;
            font-size: 14px;
        }
        
        .invoice-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .invoice-status.pagada {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .invoice-status.pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .invoice-status.fallida {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 12px;
            color: #6b7280;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .section-content {
            font-size: 14px;
            color: #1f2937;
            line-height: 1.6;
        }
        
        .company-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .company-card {
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 6px;
        }
        
        .company-card h3 {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .company-card p {
            font-size: 13px;
            color: #6b7280;
            margin: 3px 0;
        }
        
        .payment-method {
            padding: 15px;
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        
        .payment-method p {
            margin: 5px 0;
            font-size: 14px;
            color: #1f2937;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        
        table thead {
            background-color: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #374151;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .totals-card {
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .total-row.final {
            border-bottom: none;
            border-top: 2px solid #1f2937;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-button button {
            padding: 10px 20px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
            
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- HEADER --}}
        <div class="header">
            <div class="header-left">
                <h1>Nomitech</h1>
                <p><strong>Nomitech S.A.S</strong></p>
                <p>NIT: 901.555.222-4</p>
                <p>Carrera 45 # 100 - 20, Bogotá D.C.</p>
                <p>contacto@nomitech.co</p>
            </div>
            <div class="header-right">
                <div class="invoice-number">{{ $numeroFactura }}</div>
                <div class="invoice-date">Fecha: {{ $fechaPago }}</div>
                <div class="invoice-status {{ strtolower($pago->estado_pago === 'succeeded' ? 'pagada' : ($pago->estado_pago === 'pending' ? 'pendiente' : 'fallida')) }}">
                    {{ $pago->estado_pago === 'succeeded' ? 'PAGADA' : ($pago->estado_pago === 'pending' ? 'PENDIENTE' : 'FALLIDA') }}
                </div>
            </div>
        </div>

        {{-- CLIENTE INFO --}}
        <div class="company-info">
            <div class="company-card">
                <div class="section-title">Facturado A</div>
                <h3>{{ $pago->empresa->razon_social ?? 'Sin especificar' }}</h3>
                <p>NIT: {{ $pago->empresa->nit ?? 'No especificado' }}</p>
                <p>{{ $pago->empresa->direccion ?? 'No especificada' }}</p>
            </div>

            <div class="payment-method">
                <div class="section-title">Método de Pago</div>
                <p><strong>{{ $pago->proveedor_pago ?? 'No especificado' }}</strong></p>
                <p style="font-size: 12px;">Referencia: {{ $pago->referencia ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- ITEMS --}}
        <div class="section">
            <div class="section-title">Concepto</div>
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $pago->licencia->plan->nombre ?? 'Licencia Nomitech' }}</strong>
                            <p style="font-size: 12px; color: #6b7280; margin-top: 5px;">
                                Suscripción por {{ $meses }} meses - {{ $pago->licencia->plan->descripcion ?? 'Plan estándar' }}
                            </p>
                        </td>
                        <td class="text-right">1</td>
                        <td class="text-right">${{ number_format($subtotal, 2, ',', '.') }}</td>
                        <td class="text-right"><strong>${{ number_format($subtotal, 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- TOTALS --}}
        <div class="totals">
            <div class="totals-card">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($subtotal, 2, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span>IVA (19%):</span>
                    <span>${{ number_format($iva, 2, ',', '.') }}</span>
                </div>
                <div class="total-row final">
                    <span>Total:</span>
                    <span>${{ number_format($total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <p>Gracias por confiar en Nomitech. Esta es una factura electrónica válida.</p>
            <p style="margin-top: 10px;">© 2026 Nomitech S.A.S - Todos los derechos reservados.</p>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">🖨️ Imprimir / Descargar como PDF</button>
    </div>
</body>
</html>
