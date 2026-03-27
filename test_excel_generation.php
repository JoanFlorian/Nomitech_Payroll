<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Exports\PilaTxtToExcelExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    echo "🔍 Generando Excel desde archivo ejemplo_pila.txt...\n\n";
    
    $rutaArchivo = storage_path('app/ejemplo_pila.txt');
    
    if (!file_exists($rutaArchivo)) {
        echo "❌ Archivo no encontrado: $rutaArchivo\n";
        exit(1);
    }
    
    echo "✅ Archivo encontrado\n";
    
    // Crear export
    $export = new PilaTxtToExcelExport($rutaArchivo);
    echo "✅ Export creado\n";
    
    // Generar spreadsheet
    $spreadsheet = $export->export();
    echo "✅ Spreadsheet generado\n";
    
    // Obtener hoja activa
    $sheet = $spreadsheet->getActiveSheet();
    echo "✅ Hoja obtenida: " . $sheet->getTitle() . "\n\n";
    
    // Mostrar contenido
    echo "📊 CONTENIDO DEL EXCEL:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    
    for ($fila = 1; $fila <= 20; $fila++) {
        $contenido = [];
        for ($col = 'A'; $col <= 'M'; $col++) {
            $celda = $sheet->getCell("{$col}{$fila}");
            $valor = (string)$celda->getValue();
            if (!empty($valor)) {
                $contenido[] = "{$col}: {$valor}";
            }
        }
        
        if (!empty($contenido)) {
            echo "\n【Fila $fila】\n";
            foreach ($contenido as $item) {
                echo "  $item\n";
            }
        }
    }
    
    echo "\n═══════════════════════════════════════════════════════════════\n";
    
    // Guardar
    $rutaSalida = storage_path('app/exports/test_pila.xlsx');
    $writer = new Xlsx($spreadsheet);
    $writer->save($rutaSalida);
    
    echo "\n✅ Archivo guardado en: $rutaSalida\n";
    echo "📦 Tamaño: " . filesize($rutaSalida) . " bytes\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
