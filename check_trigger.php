<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Verificar triggers
$triggers = DB::select('SHOW TRIGGERS');
echo "=== TRIGGERS EN LA TABLA CONTRATO ===\n";
foreach($triggers as $t) {
    if ($t->Table === 'contrato') {
        echo "Trigger: " . $t->Trigger . "\n";
        echo "Timing: " . $t->Timing . "\n";
        echo "Event: " . $t->Event . "\n";
        echo "Statement:\n" . $t->Statement . "\n\n";
    }
}

// Verificar historial actual
$count = DB::table('historial_contrato')->count();
echo "=== HISTORIAL CONTRATO ===\n";
echo "Registros actuales: " . $count . "\n\n";

// Obtener un contrato para probar
$contrato = DB::table('contrato')->first();
if ($contrato) {
    echo "=== PROBANDO TRIGGER ===\n";
    echo "Contrato ID: " . $contrato->id_contrato . "\n";
    echo "EPS actual: " . ($contrato->id_eps ?? 'NULL') . "\n";
    echo "AFP actual: " . ($contrato->id_afp ?? 'NULL') . "\n";
    echo "Salario actual: " . $contrato->salario_base . "\n\n";
    
    // Hacer un UPDATE de prueba al salario (sumando 1 peso y luego restando)
    $nuevoSalario = $contrato->salario_base + 1;
    echo "Actualizando salario a: " . $nuevoSalario . "\n";
    
    DB::table('contrato')
        ->where('id_contrato', $contrato->id_contrato)
        ->update(['salario_base' => $nuevoSalario]);
    
    // Verificar si se creó el historial
    $historial = DB::table('historial_contrato')
        ->where('id_contrato', $contrato->id_contrato)
        ->orderByDesc('id_historial')
        ->first();
    
    if ($historial) {
        echo "HISTORIAL CREADO!\n";
        echo "- Dato anterior: " . $historial->dato_anterior . "\n";
        echo "- Dato nuevo: " . $historial->dato_nuevo . "\n";
        echo "- Tipo: " . $historial->tipo_novedad . "\n";
    } else {
        echo "NO SE CREO HISTORIAL - El trigger no funciona\n";
    }
    
    // Revertir el cambio
    DB::table('contrato')
        ->where('id_contrato', $contrato->id_contrato)
        ->update(['salario_base' => $contrato->salario_base]);
    
    echo "\nSalario revertido a: " . $contrato->salario_base . "\n";
    
    // Contar registros finales
    $countFinal = DB::table('historial_contrato')->count();
    echo "\nRegistros en historial después de prueba: " . $countFinal . "\n";
} else {
    echo "No hay contratos para probar\n";
}
