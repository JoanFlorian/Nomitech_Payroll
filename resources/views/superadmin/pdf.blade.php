<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $pago->referencia ?? 'FAC-' . $pago->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #333; background: white; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 3px solid #0066cc; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 32px; font-weight: bold; color: #0066cc; margin-bottom: 5px; }
        .title { font-size: 24px; font-weight: bold; color: #333; margin: 10px 0; }
        .invoice-number { font-size: 14px; color: #666; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 14px; font-weight: bold; color: #0066cc; text-transform: uppercase; border-bottom: 2px solid #0066cc; padding-bottom: 5px; margin-bottom: 15px; }
        .row { display: flex; margin-bottom: 8px; }
        .label { width: 150px; font-weight: bold; color: #666; }
        .value { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold; }
        td { border: 1px solid #ddd; padding: 10px; }
        .text-right { text-align: right; }
        .totals { margin-top: 20px; text-align: right; }
        .total-row { display: flex; justify-content: flex-end; margin-bottom: 8px; }
        .total-label { width: 250px; font-weight: bold; }
        .total-value { width: 150px; }
        .grand-total { font-size: 18px; font-weight: bold; color: #0066cc; border-top: 3px solid #0066cc; padding-top: 10px; margin-top: 10px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">NOMITECH</div>
            <div class="title">FACTURA DE COMPRA</div>
            <div class="invoice-number">Factura: {{ $pago->referencia ?? 'FAC-' . $pago->id }}</div>
        </div>

        <div class="section">
            <div class="section-title">Información de Factura</div>
            <div class="row">
                <div class="label">Número:</div>
                <div class="value">{{ $pago->referencia ?? 'FAC-' . $pago->id }}</div>
            </div>
            <div class="row">
                <div class="label">Fecha:</div>
                <div class="value">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="row">
                <div class="label">Estado:</div>
                <div class="value">{{ $estadoTexto }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Empresa Emisora</div>
            <div class="row">
                <div class="label">Empresa:</div>
                <div class="value">Nomitech SAS</div>
            </div>
            <div class="row">
                <div class="label">NIT:</div>
                <div class="value">9012345678</div>
            </div>
            <div class="row">
                <div class="label">Dirección:</div>
                <div class="value">Cra 10 #45-67, Bogotá, Colombia</div>
            </div>
            <div class="row">
                <div class="label">Email:</div>
                <div class="value">facturacion@nomitech.com</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Facturado A</div>
            <div class="row">
                <div class="label">Empresa:</div>
                <div class="value">{{ $empresa->razon_social ?? 'Sin especificar' }}</div>
            </div>
            <div class="row">
                <div class="label">NIT:</div>
                <div class="value">{{ $empresa->nit ?? 'No especificado' }}</div>
            </div>
            <div class="row">
                <div class="label">Dirección:</div>
                <div class="value">{{ $empresa->direccion ?? 'No especificado' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Concepto</div>
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th style="width: 100px; text-align: right;">Cantidad</th>
                        <th style="width: 150px; text-align: right;">Precio Unitario</th>
                        <th style="width: 150px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $licencia->plan->nombre ?? 'Plan de suscripción' }}</strong><br>
                            <small>{{ $licencia->plan->descripcion ?? 'Acceso a plataforma Nomitech' }}</small>
                        </td>
                        <td class="text-right">1</td>
                        <td class="text-right">${{ number_format($pago->valor, 2, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($pago->valor, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="totals">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">${{ number_format($pago->valor, 2, ',', '.') }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">IVA (19%):</div>
                <div class="total-value">$0,00</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">TOTAL:</div>
                <div class="total-value">${{ number_format($pago->valor, 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="footer">
            <p>Gracias por su compra. Esta factura es válida como comprobante de pago en Nomitech.</p>
            <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p class="no-print" style="margin-top: 20px; color: #333; font-weight: bold;">Para descargar en PDF: Use Imprimir (Ctrl+P) → Guardar como PDF</p>
        </div>
    </div>
</body>
</html>
