<?php

// Leer archivo de texto
$archivoTxt = 'ejemplo_pila.txt';
$lineas = file($archivoTxt, FILE_SKIP_EMPTY_LINES);

echo "📊 ANÁLISIS DE ESTRUCTURA PILA\n";
echo "".str_repeat("=", 100)."\n\n";

echo "Archivo: $archivoTxt\n";
echo "Total de líneas: ".count($lineas)."\n\n";

foreach ($lineas as $idx => $linea) {
    $linea = trim($linea);
    $campos = explode('|', $linea);
    $numCampos = count($campos);
    
    echo "📍 Línea ".($idx+1).": {$numCampos} COLUMNAS\n";
    echo str_repeat("-", 100)."\n";
    
    // Mostrar cada campo
    for ($i = 0; $i < $numCampos; $i++) {
        $valor = $campos[$i];
        $longitud = strlen($valor);
        $tipo = is_numeric($valor) && $valor !== '' ? 'NÚMERO' : 'TEXTO';
        echo sprintf("[%2d] %-15s (len:%3d) = '%s'\n", $i, $tipo, $longitud, $valor);
    }
    echo "\n";
}

echo "\n✅ ESPERADO EN EXCEL:\n";
echo "- Cada línea en una fila\n";
echo "- Cada campo (separado por |) en una columna diferente\n";
echo "- Valores exactos sin modificación\n";
echo "- Todo formato de TEXTO (sin interpretación de números)\n";
echo "- Nombres y valores completamente visibles\n";
