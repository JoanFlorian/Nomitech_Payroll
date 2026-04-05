<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Banco;

$bancosACH = [
    'BANCO DE BOGOTA' => '1001',
    'BANCOLOMBIA' => '1007',
    'DAVIPLATA' => '1551',
    'NEQUI' => '1507',
    'BANCO DAVIVIENDA' => '1051',
    'BANCO DE OCCIDENTE' => '1023',
    'BANCO POPULAR' => '1002',
    'BANCO AV VILLAS' => '1052',
];

echo "Updating bank ACH codes...\n";

foreach ($bancosACH as $nombre => $codigo) {
    $banco = Banco::where('nombre', 'like', "%$nombre%")->first();
    if ($banco) {
        if ($banco->codigo_ach !== $codigo) {
            $banco->update(['codigo_ach' => $codigo]);
            echo "Updated: {$banco->nombre} -> ACH: {$codigo}\n";
        } else {
            echo "Already correct: {$banco->nombre} -> ACH: {$codigo}\n";
        }
    } else {
        echo "Not found: $nombre\n";
    }
}

echo "Done.\n";
