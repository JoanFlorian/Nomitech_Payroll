<?php

namespace App\Services;

use App\Models\Empresa;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class PilaExportService
{
    /**
     * Exportar historial completo a Excel
     */
    public function exportarHistorialExcel(array $datos, Empresa $empresa)
    {
        $export = new \App\Exports\PilaHistorialExport($datos, $empresa);
        $spreadsheet = $export->export();
        
        return $this->generarResponse($spreadsheet, 'historial-pila', $empresa);
    }

    /**
     * Exportar un registro individual a Excel
     */
    public function exportarRegistroExcel(array $registro, Empresa $empresa)
    {
        $export = new \App\Exports\PilaRegistroExport($registro, $empresa);
        $spreadsheet = $export->export();
        
        return $this->generarResponse($spreadsheet, 'registro-pila', $empresa);
    }

    /**
     * Generar respuesta de descarga
     */
    private function generarResponse($spreadsheet, string $tipo, Empresa $empresa)
    {
        $filename = $tipo . '-' . $empresa->nit . '-' . now()->format('Ymd_His') . '.xlsx';
        
        // Generar en Stream directo sin archivo temporal
        return response()->stream(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
