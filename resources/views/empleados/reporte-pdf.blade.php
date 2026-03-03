<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Empleados</title>
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
        <div class="title">Reporte de Empleados</div>
        <div class="meta">Generado: {{ $fechaGeneracion }}</div>
        <div class="meta">Filtro estado: {{ $filtroEstado }} | Búsqueda: {{ $busqueda }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Documento</th>
                <th>Nombre Completo</th>
                <th>Email</th>
                <th>Tipo Contrato</th>
                <th class="text-right">Salario Base</th>
                <th>Estado</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usuarios as $usuario)
                @php
                    $contrato = $usuario->contratos->first();
                    $nombreCompleto = trim(
                        $usuario->primer_nombre . ' ' .
                        ($usuario->otros_nombres ? $usuario->otros_nombres . ' ' : '') .
                        $usuario->primer_apellido . ' ' .
                        ($usuario->segundo_apellido ?? '')
                    );
                    $estado = $contrato ? ($contrato->activo ? 'Activo' : 'Inactivo') : 'Sin contrato';
                @endphp
                <tr>
                    <td>{{ $usuario->doc }}</td>
                    <td>{{ $nombreCompleto }}</td>
                    <td>{{ $usuario->email ?? '' }}</td>
                    <td>{{ $contrato?->tipoContrato?->nombre ?? 'Sin contrato' }}</td>
                    <td class="text-right">${{ number_format((float) ($contrato?->salario_base ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $estado }}</td>
                    <td>{{ $contrato?->fecha_inicio ?? '' }}</td>
                    <td>{{ $contrato?->fecha_fin ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">No hay empleados para exportar con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
