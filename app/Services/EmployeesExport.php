<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class EmployeesExport
{
    /**
     * Exportar empleados activos a CSV/Excel
     * Retorna un stream de descarga
     */
    public static function export()
    {
        // Obtener empleados activos con sus contratos
        $usuarios = Usuario::with('contratos')
            ->whereHas('contratos', function ($q) {
                $q->where('activo', true);
            })
            ->get();

        // Preparar headers para descarga de Excel (CSV)
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="empleados_' . date('Y-m-d_H-i-s') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => '0',
        ];

        // Crear el callback para generar el CSV
        $callback = function () use ($usuarios) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM para Excel (Windows)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers de columnas
            $headers = [
                'Número de Documento',
                'Primer Nombre',
                'Primer Apellido',
                'Según do Apellido',
                'Nombre Completo',
                'Salario Base',
                'Tipo Contrato',
                'Fecha Inicio',
                'Fecha Fin',
                'Alto Riesgo',
                'Activo',
            ];

            // Escribir headers
            fputcsv($file, $headers, ';');

            // Escribir datos
            foreach ($usuarios as $usuario) {
                $contrato = $usuario->contratos->first();

                if (!$contrato) {
                    continue;
                }

                $nombreCompleto = trim(
                    $usuario->primer_nombre . ' ' .
                    ($usuario->otros_nombres ? $usuario->otros_nombres . ' ' : '') .
                    $usuario->primer_apellido . ' ' .
                    ($usuario->segundo_apellido ?? '')
                );

                $row = [
                    $usuario->doc,
                    $usuario->primer_nombre,
                    $usuario->primer_apellido,
                    $usuario->segundo_apellido ?? '',
                    $nombreCompleto,
                    $contrato->salario_base ?? '',
                    $contrato->id_tipo_contrato ?? '',
                    $contrato->fecha_inicio ?? '',
                    $contrato->fecha_fin ?? '',
                    $contrato->alto_riesgo ? 'Sí' : 'No',
                    $contrato->activo ? 'Sí' : 'No',
                ];

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
