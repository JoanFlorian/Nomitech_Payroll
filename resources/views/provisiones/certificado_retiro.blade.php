<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado de Autorización de Retiro de Cesantías</title>
    <style>
        @page {
            margin: 2cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 13px;
            line-height: 1.6;
            color: #222;
            max-width: 700px;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 5px;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #555;
            margin: 0;
        }

        .company-info {
            text-align: center;
            margin-bottom: 25px;
            font-size: 12px;
            color: #444;
        }

        .body-text {
            text-align: justify;
            margin-bottom: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .data-table td {
            padding: 8px 12px;
            border: 1px solid #ccc;
        }

        .data-table td:first-child {
            font-weight: bold;
            width: 40%;
            background: #f9f9f9;
        }

        .signature-block {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }

        .signature-line {
            text-align: center;
            width: 45%;
        }

        .signature-line hr {
            border: none;
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }

        .signature-line p {
            margin: 2px 0;
            font-size: 11px;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 10px 25px; background: #1565C0; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
            <strong>Imprimir / Guardar como PDF</strong>
        </button>
    </div>

    <div class="header">
        <h1>Certificado de Autorización</h1>
        <h2>Retiro Parcial de Cesantías</h2>
    </div>

    <div class="company-info">
        <strong>{{ $empresa->razon_social ?? 'Empresa' }}</strong><br>
        NIT: {{ $empresa->nit ?? 'N/A' }}{{ $empresa->nit_dv ? '-' . $empresa->nit_dv : '' }}
    </div>

    <div class="body-text">
        <p>
            Por medio de la presente, <strong>{{ $empresa->razon_social ?? 'la empresa' }}</strong>,
            identificada con NIT
            <strong>{{ $empresa->nit ?? 'N/A' }}{{ $empresa->nit_dv ? '-' . $empresa->nit_dv : '' }}</strong>,
            certifica y autoriza el retiro parcial de cesantías del(la) trabajador(a):
        </p>
    </div>

    <table class="data-table">
        <tr>
            <td>Nombre del Empleado</td>
            <td>{{ $employee->nombre_completo ?? ($employee->primer_nombre . ' ' . $employee->primer_apellido) }}</td>
        </tr>
        <tr>
            <td>Documento de Identidad</td>
            <td>{{ $employee->doc }}</td>
        </tr>
        <tr>
            <td>Motivo del Retiro</td>
            <td>{{ $reasonLabel }}</td>
        </tr>
        <tr>
            <td>Monto Autorizado</td>
            <td>${{ number_format($withdrawal->amount, 2, ',', '.') }} COP</td>
        </tr>
        <tr>
            <td>Saldo Actual de Cesantías</td>
            <td>${{ number_format($currentBalance, 2, ',', '.') }} COP</td>
        </tr>
        <tr>
            <td>Origen del Pago</td>
            <td>{{ $withdrawal->payment_origin === 'fund' ? 'Fondo de Cesantías' : 'Empresa (pago directo)' }}</td>
        </tr>
        <tr>
            <td>Fondo de Cesantías</td>
            <td>{{ $employee->fondo_cesantias ?? 'No asignado' }}</td>
        </tr>
        <tr>
            <td>Fecha de Emisión</td>
            <td>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="body-text">
        <p>
            Este certificado se expide de conformidad con lo establecido en el artículo 256 del
            Código Sustantivo del Trabajo y sus normas reglamentarias, para ser presentado
            ante el fondo de cesantías correspondiente.
        </p>
    </div>

    <div class="signature-block">
        <div class="signature-line">
            <hr>
            <p><strong>Firma del Empleador</strong></p>
            <p>Representante Legal</p>
            <p>{{ $empresa->razon_social ?? '' }}</p>
        </div>
        <div class="signature-line">
            <hr>
            <p><strong>Firma del Trabajador</strong></p>
            <p>{{ $employee->nombre_completo ?? ($employee->primer_nombre . ' ' . $employee->primer_apellido) }}</p>
            <p>Doc: {{ $employee->doc }}</p>
        </div>
    </div>

    <div class="footer">
        Documento generado automáticamente por Nomitech el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}.
        Certificado ID: {{ $withdrawal->id }}
    </div>

</body>

</html>