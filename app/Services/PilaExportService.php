<?php

namespace App\Services;

use App\Models\Empresa;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PilaExportService
{
    /**
     * Exportar historial completo a Excel
     */
    public function exportarHistorialExcel(array $datos, Empresa $empresa)
    {
        $export = new \App\Exports\PilaHistorialExport($datos, $empresa);
        $spreadsheet = $export->export();

        // Validación clave
        if (!$spreadsheet instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet) {
            throw new \Exception('El export no generó un Spreadsheet válido.');
        }

        return $this->generarResponse($spreadsheet, 'historial-pila', $empresa);
    }

    /**
     * Exportar un registro individual a Excel
     */
    public function exportarRegistroExcel(array $registro, Empresa $empresa)
    {
        $export = new \App\Exports\PilaRegistroExport($registro, $empresa);
        $spreadsheet = $export->export();

        // Validación clave
        if (!$spreadsheet instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet) {
            throw new \Exception('El export no generó un Spreadsheet válido.');
        }

        return $this->generarResponse($spreadsheet, 'registro-pila', $empresa);
    }

    /**
     * Exportar archivo PILA .txt a Excel
     * 
     * @param string $rutaArchivo Ruta al archivo txt PILA
     * @param Empresa|null $empresa Empresa para información adicional
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportarPilaTxtAExcel(string $rutaArchivo, ?Empresa $empresa = null)
    {
        $export = new \App\Exports\PilaTxtToExcelExport($rutaArchivo);
        $spreadsheet = $export->export();

        // Validación clave
        if (!$spreadsheet instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet) {
            throw new \Exception('El export no generó un Spreadsheet válido.');
        }

        $nombreArchivo = 'planilla-pila-' . ($empresa?->nit ?? 'sin-nit') . '-' . now()->format('Ymd_His');

        return $this->generarResponse($spreadsheet, $nombreArchivo, $empresa ?? new \stdClass());
    }

    /**
     * Exportar archivo PILA .txt a Excel (versión simple sin formatos)
     * Mantiene exactamente los valores numéricos sin cambios
     * 
     * @param string $rutaArchivo Ruta al archivo txt PILA
     * @param Empresa|null $empresa Empresa para información adicional
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportarPilaTxtAExcelSimple(string $rutaArchivo, ?Empresa $empresa = null)
    {
        $export = new \App\Exports\PilaTxtToExcelSimple($rutaArchivo);
        $spreadsheet = $export->export();

        // Validación clave
        if (!$spreadsheet instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet) {
            throw new \Exception('El export no generó un Spreadsheet válido.');
        }

        $nombreArchivo = 'pila-' . ($empresa?->nit ?? 'documento') . '-' . now()->format('Ymd_His');

        return $this->generarResponse($spreadsheet, $nombreArchivo, $empresa ?? new \stdClass());
    }

    /**
     * Exportar archivo PILA .txt a Excel (versión con diseño profesional)
     * Mantiene exactamente las columnas y valores del archivo de texto, pero con estilos
     * 
     * @param string $rutaArchivo Ruta al archivo txt PILA
     * @param Empresa|null $empresa Empresa para información adicional
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportarPilaTxtAExcelWithDesign(string $rutaArchivo, ?Empresa $empresa = null)
    {
        $export = new \App\Exports\PilaTxtToExcelWithDesign($rutaArchivo);
        $spreadsheet = $export->export();

        // Validación clave
        if (!$spreadsheet instanceof \PhpOffice\PhpSpreadsheet\Spreadsheet) {
            throw new \Exception('El export no generó un Spreadsheet válido.');
        }

        $nombreArchivo = 'pila-diseño-' . ($empresa?->nit ?? 'documento') . '-' . now()->format('Ymd_His');

        return $this->generarResponse($spreadsheet, $nombreArchivo, $empresa ?? new \stdClass());
    }

    /**
     * Generar respuesta de descarga (VERSIÓN SEGURA)
     */
    private function generarResponse($spreadsheet, string $tipo, $empresa)
    {
        $nit = $empresa?->nit ?? 'documento';
        $filename = $tipo . '-' . $nit . '-' . now()->format('Ymd_His') . '.xlsx';

        return response()->stream(
            function () use ($spreadsheet) {

                // 🔥 CRÍTICO: limpiar cualquier salida previa
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                // 🔥 Evita errores invisibles que dañan el Excel
                error_reporting(0);

                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // 🔥 Cerrar correctamente
                flush();
                exit;
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
                'Pragma' => 'public',
            ]
        );
    }
}