<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Leer archivo de texto
$archivoTxt = 'ejemplo_pila.txt';
$lineasTxt = file($archivoTxt, FILE_SKIP_EMPTY_LINES);

echo "📄 VERIFICACIÓN DE CONVERSION PILA\n";
echo "=".str_repeat("=", 80)."\n\n";

// Analizar archivo de texto
echo "1️⃣  ARCHIVO DE TEXTO: $archivoTxt\n";
echo "-".str_repeat("-", 80)."\n";

foreach ($lineasTxt as $idx => $linea) {
    $linea = trim($linea);
    $campos = explode('|', $linea);
    $numCampos = count($campos);
    
    echo sprintf("Línea %d: %d columnas\n", $idx + 1, $numCampos);
    
    // Mostrar primeros 3 campos de cada línea
    for ($i = 0; $i < min(3, $numCampos); $i++) {
        $valor = $campos[$i];
        echo sprintf("  [%d] = '%s'\n", $i, $valor);
    }
    echo "\n";
}

// Leer archivo Excel generado
echo "\n2️⃣  ARCHIVO EXCEL GENERADO: storage/app/exports/pila_test_v2.xlsx\n";
echo "-".str_repeat("-", 80)."\n";

$archivoExcel = 'storage/app/exports/pila_test_v2.xlsx';

if (!file_exists($archivoExcel)) {
    echo "❌ Archivo Excel no encontrado: $archivoExcel\n";
    exit(1);
}

$spreadsheet = IOFactory::load($archivoExcel);
$sheet = $spreadsheet->getActiveSheet();

$maxRow = $sheet->getHighestRow();
$maxCol = $sheet->getHighestColumn();

echo "Dimensiones: {$maxCol}{$maxRow}\n";
echo "Máxima columna: $maxCol\n";
echo "Máximas filas: $maxRow\n\n";

// Leer datos del Excel y compararlos con el texto
foreach (range(1, min(5, $maxRow)) as $row) {
    $colIndex = 0;
    $valoresExcel = [];
    
    // Leer todas las columnas de esta fila
    for ($col = 'A'; ord($col) <= ord($maxCol); $col++) {
        $cell = $sheet->getCell("{$col}{$row}");
        $valor = $cell->getValue();
        $valoresExcel[] = $valor;
    }
    
    echo sprintf("Fila %d Excel: %d columnas\n", $row, count($valoresExcel));
    for ($i = 0; $i < min(5, count($valoresExcel)); $i++) {
        echo sprintf("  [%d] = '%s'\n", $i, $valoresExcel[$i]);
    }
    echo "\n";
    
    // Comparar con el texto
    $linea = trim($lineasTxt[$row - 1]);
    $camposTxt = explode('|', $linea);
    
    echo "Comparación fila $row:\n";
    echo sprintf("  Texto:  %d campos\n", count($camposTxt));
    echo sprintf("  Excel:  %d columnas\n", count($valoresExcel));
    
    if (count($camposTxt) !== count($valoresExcel)) {
        echo "  ⚠️  DIFERENCIA EN CANTIDAD DE CAMPOS\n";
    }
    
    // Comparar valores
    $mismosValores = true;
    for ($i = 0; $i < count($camposTxt) && $i < count($valoresExcel); $i++) {
        if ($camposTxt[$i] !== $valoresExcel[$i]) {
            echo "  ❌ Campo $i diferente:\n";
            echo sprintf("     Texto: '%s'\n", $camposTxt[$i]);
            echo sprintf("     Excel: '%s'\n", $valoresExcel[$i]);
            $mismosValores = false;
        }
    }
    
    if ($mismosValores && count($camposTxt) === count($valoresExcel)) {
        echo "  ✅ Todos los valores coinciden\n";
    }
    echo "\n";
}

echo "\n✅ Verificación completada\n";
