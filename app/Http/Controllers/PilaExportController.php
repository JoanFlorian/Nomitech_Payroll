<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\PilaExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PilaExportController extends Controller
{
    private PilaExportService $exportService;

    public function __construct(PilaExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Descargar historial completo de PILA en Excel
     */
    public function descargarHistorialExcel(Request $request)
    {
        try {
            $empresaId = (int) (session('empresa_id') ?: 0);
            
            if ($empresaId <= 0) {
                return back()->withErrors([
                    'pila' => 'No se pudo identificar la empresa activa de la sesión.',
                ]);
            }

            if (!Schema::hasTable('pila_archivos')) {
                return back()->withErrors([
                    'pila' => 'No existe historial de archivos PILA en este entorno.',
                ]);
            }

            // Obtener datos del historial
            $historialPila = DB::table('pila_archivos as pa')
                ->leftJoin('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'pa.periodo_id')
                ->where('pa.empresa_id', $empresaId)
                ->orderByDesc('pa.id')
                ->get([
                    'pa.id',
                    'pa.periodo_id',
                    'pa.empresa_id',
                    'pa.nombre_archivo',
                    'pa.ruta_archivo',
                    'pa.total_empleados',
                    'pa.created_at',
                    'pl.fecha_inicio',
                    'pl.fecha_fin',
                ]);

            if ($historialPila->isEmpty()) {
                return back()->withErrors([
                    'pila' => 'No hay historial de archivos PILA para descargar.',
                ]);
            }

            // Obtener datos de la empresa
            $empresa = Empresa::find($empresaId);
            if (!$empresa) {
                return back()->withErrors([
                    'pila' => 'No se encontró la empresa para generar el reporte.',
                ]);
            }

            // Generar exportación a través del servicio
            return $this->exportService->exportarHistorialExcel(
                $historialPila->toArray(),
                $empresa
            );

        } catch (\Exception $e) {
            return back()->withErrors([
                'pila' => 'Error al generar la exportación: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Descargar registro individual de PILA en Excel
     */
    public function descargarRegistroExcel(int $id, Request $request)
    {
        try {
            $empresaId = (int) (session('empresa_id') ?: 0);

            if ($empresaId <= 0) {
                return back()->withErrors([
                    'pila' => 'No se pudo identificar la empresa activa.',
                ]);
            }

            // Obtener el registro específico
            $registro = DB::table('pila_archivos as pa')
                ->leftJoin('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'pa.periodo_id')
                ->where('pa.id', $id)
                ->where('pa.empresa_id', $empresaId)
                ->first([
                    'pa.id',
                    'pa.periodo_id',
                    'pa.empresa_id',
                    'pa.nombre_archivo',
                    'pa.ruta_archivo',
                    'pa.total_empleados',
                    'pa.created_at',
                    'pl.fecha_inicio',
                    'pl.fecha_fin',
                ]);

            if (!$registro) {
                return back()->withErrors([
                    'pila' => 'El registro PILA no fue encontrado.',
                ]);
            }

            // Obtener datos de la empresa
            $empresa = Empresa::find($empresaId);
            if (!$empresa) {
                return back()->withErrors([
                    'pila' => 'No se encontró la empresa para generar el reporte.',
                ]);
            }

            // Generar exportación de un solo registro
            return $this->exportService->exportarRegistroExcel(
                (array) $registro,
                $empresa
            );

        } catch (\Exception $e) {
            return back()->withErrors([
                'pila' => 'Error al generar la exportación: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Vista previa de la exportación (JSON)
     */
    public function previewHistorial(Request $request): array
    {
        $empresaId = (int) (session('empresa_id') ?: 0);

        if ($empresaId <= 0) {
            return ['error' => 'No se identificó la empresa activa'];
        }

        if (!Schema::hasTable('pila_archivos')) {
            return ['error' => 'No existe historial de archivos PILA'];
        }

        $historialPila = DB::table('pila_archivos as pa')
            ->leftJoin('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'pa.periodo_id')
            ->where('pa.empresa_id', $empresaId)
            ->orderByDesc('pa.id')
            ->limit(10)
            ->get([
                'pa.id',
                'pa.periodo_id',
                'pa.total_empleados',
                'pa.created_at',
                'pl.fecha_inicio',
                'pl.fecha_fin',
            ]);

        return [
            'success' => true,
            'total' => $historialPila->count(),
            'data' => $historialPila
        ];
    }

    /**
     * TEST: Excel básico sin datos externos
     */
    public function testExcelMinimal()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Test');

            $sheet->setCellValue('A1', 'Prueba');
            $sheet->setCellValue('B1', '123');
            $sheet->setCellValue('A2', 'Dato2');
            $sheet->setCellValue('B2', '456');

            return response()->stream(
                function () use ($spreadsheet) {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="test-basico.xlsx"',
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * TEST: Excel con datos DB pero sin formato
     */
    public function testExcelWithData()
    {
        try {
            $empresaId = (int) (session('empresa_id') ?: 0);
            
            if ($empresaId <= 0) {
                return response()->json(['error' => 'Sin empresa'], 400);
            }

            $registro = DB::table('pila_archivos')
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$registro) {
                return response()->json(['error' => 'Sin datos PILA'], 404);
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Test');

            $row = 1;
            foreach ((array) $registro as $key => $value) {
                if ($value === null) {
                    $value = '';
                }
                $value = (string) $value;
                
                $sheet->setCellValue('A' . $row, $key);
                $sheet->setCellValue('B' . $row, $value);
                $row++;
            }

            return response()->stream(
                function () use ($spreadsheet) {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="test-datos.xlsx"',
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
