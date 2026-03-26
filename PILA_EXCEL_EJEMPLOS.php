<?php

/**
 * ====================================
 * EJEMPLOS DE USO: PilaTxtToExcelExport
 * ====================================
 * 
 * Esta clase convierte archivos PILA .txt a Excel profesional
 * usando PhpSpreadsheet (sin Laravel Excel)
 */

namespace App\Examples;

use App\Exports\PilaTxtToExcelExport;
use App\Models\Empresa;
use App\Services\PilaExportService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class EjemplosPilaTxtToExcel
{
    /**
     * EJEMPLO 1: Usar directamente la clase export
     * Ideal cuando necesitas el Spreadsheet para procesamiento adicional
     */
    public static function ejemplo1_DirectoExport(): void
    {
        // Ruta del archivo .txt PILA
        $rutaArchivo = storage_path('app/pila/planilla_pila_123_45_20260326_120000.txt');

        try {
            // Crear instancia y procesar
            $export = new PilaTxtToExcelExport($rutaArchivo);
            $spreadsheet = $export->export();

            // Ahora tienes el Spreadsheet como objeto
            // Puedes hacer más cosas antes de guardar/descargar

            // Guardar localmente
            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path('app/exports/planilla.xlsx'));

            echo "✅ Excel generado: storage/app/exports/planilla.xlsx";
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }

    /**
     * EJEMPLO 2: Usar método estático para exportación rápida
     */
    public static function ejemplo2_MetodoEstatico(): void
    {
        $rutaArchivo = storage_path('app/pila/planilla_pila_123_45_20260326_120000.txt');

        // Método estático que ya procesa todo
        $spreadsheet = PilaTxtToExcelExport::fromFile($rutaArchivo);

        // Descargar directamente
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="planilla.xlsx"');
        $writer->save('php://output');
        exit;
    }

    /**
     * EJEMPLO 3: Usar con el servicio existente PilaExportService
     * Recomendado: Se integra con la infraestructura existente
     */
    public static function ejemplo3_ConServicio(): void
    {
        $rutaArchivo = storage_path('app/pila/planilla_pila_123_45_20260326_120000.txt');
        
        // Obtener empresa (opcional pero recomendado para datos en el header)
        $empresa = Empresa::find(1); // tu empresa

        $service = new PilaExportService();
        
        // Esto devuelve la respuesta StreamedResponse lista para descargar
        $response = $service->exportarPilaTxtAExcel($rutaArchivo, $empresa);
        
        // En un controlador, simplemente retorna:
        return $response;
    }

    /**
     * EJEMPLO 4: En un Controlador (forma recomendada para API)
     */
    public static function ejemplo4_Controlador()
    {
        // Pseudocódigo para un controlador
        /*
        <?php
        
        namespace App\Http\Controllers;
        
        use App\Services\PilaExportService;
        use App\Models\Empresa;
        use Illuminate\Http\Request;

        class PilaExportController extends Controller
        {
            public function exportarTxtAExcel(Request $request)
            {
                $request->validate([
                    'ruta_archivo' => 'required|string|exists:storage_path',
                    'empresa_id' => 'nullable|integer|exists:empresa,id_empresa',
                ]);

                $rutaArchivo = storage_path('app/' . $request->ruta_archivo);
                $empresa = $request->empresa_id 
                    ? Empresa::find($request->empresa_id) 
                    : null;

                $service = new PilaExportService();
                return $service->exportarPilaTxtAExcel($rutaArchivo, $empresa);
            }
        }
        */
    }

    /**
     * EJEMPLO 5: Procesar múltiples archivos
     */
    public static function ejemplo5_MultiplesArchivos(): void
    {
        $archivos = [
            'pila/enero/planilla1.txt',
            'pila/enero/planilla2.txt',
            'pila/enero/planilla3.txt',
        ];

        foreach ($archivos as $archivo) {
            $ruta = storage_path("app/{$archivo}");
            
            if (!file_exists($ruta)) {
                echo "⚠️ Archivo no encontrado: {$archivo}\n";
                continue;
            }

            try {
                $export = new PilaTxtToExcelExport($ruta);
                $spreadsheet = $export->export();
                
                $nombreSalida = basename($archivo, '.txt') . '.xlsx';
                $writer = new Xlsx($spreadsheet);
                $writer->save(storage_path("app/exports/{$nombreSalida}"));
                
                echo "✅ Procesado: {$nombreSalida}\n";
            } catch (\Exception $e) {
                echo "❌ Error en {$archivo}: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * EJEMPLO 6: Personalizar el Spreadsheet después de crear
     */
    public static function ejemplo6_PersonalizarSpreadsheet(): void
    {
        $rutaArchivo = storage_path('app/pila/planilla_pila_123_45_20260326_120000.txt');

        $export = new PilaTxtToExcelExport($rutaArchivo);
        $spreadsheet = $export->export();

        // Ahora puedes hacer más personalizaciones
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar una hoja adicional con gráficos, análisis, etc.
        $analisisSheet = $spreadsheet->createSheet();
        $analisisSheet->setTitle('Análisis');
        $analisisSheet->setCellValue('A1', 'Resumen de Aportes');

        // Guardar
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/exports/planilla_completa.xlsx'));

        echo "✅ Excel personalizado generado";
    }

    /**
     * EJEMPLO 7: Manejo de errores
     */
    public static function ejemplo7_ManejErrores(): void
    {
        $rutaArchivo = 'archivo_inexistente.txt';

        try {
            $export = new PilaTxtToExcelExport($rutaArchivo);
            $spreadsheet = $export->export();

            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path('app/exports/resultado.xlsx'));

            echo "✅ Éxito";
        } catch (\Exception $e) {
            // Capturar errores
            $mensajeError = $e->getMessage();
            
            if (strpos($mensajeError, 'no encontrado') !== false) {
                echo "❌ Archivo no encontrado";
            } elseif (strpos($mensajeError, 'vacío') !== false) {
                echo "❌ El archivo está vacío";
            } elseif (strpos($mensajeError, 'encabezado') !== false) {
                echo "❌ Falta el encabezado tipo 01";
            } else {
                echo "❌ Error: {$mensajeError}";
            }

            // Log para debugging
            \Log::error('PilaTxtToExcelExport Error', ['error' => $mensajeError]);
        }
    }
}

/**
 * ====================================
 * ESTRUCTURA DEL ARCHIVO .TXT ESPERADO
 * ====================================
 * 
 * LÍNEA TIPO 01 (ENCABEZADO):
 * 01|NIT|NOMBRE_EMPRESA|NI|PERIODO|TOTAL_EMPLEADOS
 * 
 * Ejemplo:
 * 01|830012345|EMPRESA ACME SAS|NI|2026-01|15
 * 
 * 
 * LÍNEAS TIPO 02 (EMPLEADOS):
 * 02|NIT|PERIODO|TIPO_DOC|DOCUMENTO|NOMBRE|CODIGO_EPS|CODIGO_AFP|CODIGO_ARL|IBC_SALUD|DIAS_COTIZADOS|APORTE_SALUD|APORTE_PENSION|VALOR_ARL|APORTE_CAJA|APORTE_FP
 * 
 * Ejemplo:
 * 02|830012345|2026-01|CC|1070599004|JUAN PEREZ|800100017|1|860006015|2000000|30|280000|320000|43680|80000|0
 * 02|830012345|2026-01|CC|1070599005|MARIA GARCIA|800100017|1|860006015|2000000|30|280000|320000|43680|80000|0
 * 
 * 
 * CAMPOS EN DETALLE:
 * - Tipo Registro: 02
 * - NIT: Número de identificación tributaria de la empresa
 * - Periodo: Año-Mes (YYYY-MM)
 * - Tipo Documento: CC, CE, PA, etc.
 * - Documento: Documento del empleado
 * - Nombre: Nombre completo
 * - Código EPS: Código PILA de la EPS
 * - Código AFP: Código PILA del fondo de pensiones
 * - Código ARL: Código PILA de la ARL
 * - IBC Salud: Ingreso Base de Cotización para Salud
 * - Días Cotizados: Días trabajados/cotizados en el período
 * - Aporte Salud: Aporte total a salud (empleado + empleador)
 * - Aporte Pensión: Aporte total a pensión (empleado + empleador)
 * - Valor ARL: Valor del aporte a la ARL
 * - Aporte Caja: Aporte a caja de compensación
 * - Aporte FP: Aporte a fondo de pensiones (APV)
 */

/**
 * ====================================
 * CARACTERÍSTICAS DEL EXCEL GENERADO
 * ====================================
 * 
 * ✅ DISEÑO PROFESIONAL:
 *    - Encabezado con nombre de empresa, NIT, período
 *    - Títulos en negrilla con colores corporativos
 *    - Columnas perfectamente ajustadas
 *    - Bordes profesionales
 *    - Colores suaves en encabezados (azul corporativo)
 *    - Filas alternas con color de fondo suave
 * 
 * ✅ TABLA CON COLUMNAS:
 *    - Tipo Registro
 *    - Documento
 *    - Nombre
 *    - EPS
 *    - AFP
 *    - ARL
 *    - IBC (formato numérico)
 *    - Días Cotizados
 *    - Aporte Salud (formato numérico con separador de miles)
 *    - Aporte Pensión (formato numérico)
 *    - Valor ARL (formato numérico)
 *    - Aporte Caja (formato numérico)
 *    - Aporte FP (formato numérico)
 * 
 * ✅ RESUMEN FINAL:
 *    - Fila de totales con suma de todos los aportes
 *    - Formato destacado con color de fondo
 * 
 * ✅ FUNCIONALIDADES:
 *    - Filas congeladas (headers no se desplazan)
 *    - Bordes en toda la tabla
 *    - Formato numérico con separador de miles
 *    - Alineación automática (textos a izquierda, números a derecha)
 *    - Ancho de columnas optimizado
 */
