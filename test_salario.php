<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

session_start();
$_SESSION['empresa_id'] = 2;
$_SESSION['active_period_id'] = 8;

$service = $app->make(\App\Services\CalculoNovedadService::class);
$salario = $service->obtenerSalarioEmpleado('451564196854');

if ($salario) {
    echo "Salario encontrado: id={$salario->id_salario} id_periodo={$salario->id_periodo}\n";
} else {
    echo "NULL - no se encontró salario\n";
}
