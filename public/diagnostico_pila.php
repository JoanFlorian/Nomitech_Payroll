<?php
echo "<pre>"; // Para que se vea bien en el navegador
header('Content-Type: text/plain'); 

echo "🔍 INICIANDO DIAGNÓSTICO DEL MÓDULO PILA - NOMITECH\n";
echo "--------------------------------------------------\n\n";

// 1. Verificar Versión de PHP
echo "1. Versión de PHP: " . PHP_VERSION . " (Recomendado: 8.2+)\n";

// 2. Verificar Extensiones Críticas
$extensiones = [
    'iconv' => 'Limpieza de strings y sanitización PILA',
    'bcmath' => 'Cálculos de precisión (Requerido por PhpSpreadsheet)',
    'gd' => 'Manipulación de imágenes (Requerido por PhpSpreadsheet)',
    'zip' => 'Compresión de archivos Excel (Requerido por PhpSpreadsheet)',
    'mbstring' => 'Manejo de caracteres multibyte',
    'intl' => 'Internacionalización y formato de fechas'
];

echo "\n2. Verificando extensiones de PHP:\n";
foreach ($extensiones as $ext => $desc) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext: Instalada ($desc)\n";
    } else {
        echo "   ❌ $ext: NO INSTALADA ⚠️ ($desc)\n";
    }
}

// 3. Verificar Entorno Laravel y Vite
echo "\n3. Verificando manifest de Vite (Producción):\n";
$basePath = dirname(__DIR__);
$publicPath = $basePath . '/public';
$manifestPath = $publicPath . '/build/manifest.json';
$hotPath = $publicPath . '/hot';

if (file_exists($hotPath)) {
    echo "   🏃 Detectado servidor Hot Reload de Vite (.hot existe). Entorno local/dev.\n";
} elseif (file_exists($manifestPath)) {
    echo "   ✅ Manifest de Vite encontrado en build/manifest.json. Assets compilados para producción.\n";
} else {
    echo "   ❌ NO SE ENCONTRÓ MANIFEST DE VITE NI HOT RELOAD. ⚠️\n";
    echo "      Esto causará un Error 500 si la vista usa @vite y no se ha ejecutado 'npm run build'.\n";
}

// 4. Verificar Permisos de Almacenamiento
echo "\n4. Verificando permisos de escritura en Storage:\n";
$storagePath = $basePath . '/storage/app';
$pilaDir = $storagePath . '/pila';

if (is_writable($storagePath)) {
    echo "   ✅ storage/app: Escritura permitida.\n";
} else {
    echo "   ❌ storage/app: NO SE PUEDE ESCRIBIR. ⚠️ (Causará error al guardar archivos PILA)\n";
}

if (!is_dir($pilaDir)) {
    echo "   ℹ️ El directorio storage/app/pila no existe aún (se creará al primer uso).\n";
} elseif (is_writable($pilaDir)) {
    echo "   ✅ storage/app/pila: Escritura permitida.\n";
} else {
    echo "   ❌ storage/app/pila: NO SE PUEDE ESCRIBIR. ⚠️\n";
}

// 5. Verificar Archivos Críticos
echo "\n5. Verificando archivos de configuración:\n";
$envFile = $basePath . '/.env';
$pilaConfig = $basePath . '/config/pila.php';

if (file_exists($envFile)) echo "   ✅ .env: Encontrado\n"; else echo "   ❌ .env: NO ENCONTRADO ⚠️\n";
if (file_exists($pilaConfig)) echo "   ✅ config/pila.php: Encontrado\n"; else echo "   ❌ config/pila.php: NO ENCONTRADO ⚠️\n";

echo "\n--------------------------------------------------\n";
echo "🏁 DIAGNÓSTICO FINALIZADO\n";
if (extension_loaded('iconv') && (file_exists($manifestPath) || file_exists($hotPath))) {
    echo "Si los puntos anteriores son ✅, el error podría estar en la sincronización de base de datos o en datos corruptos en la tabla 'salario'.\n";
} else {
    echo "🚨 Se encontraron posibles causas del Error 500. Revise los puntos con ❌.\n";
}
