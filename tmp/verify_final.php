<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

$id_empresa = 6; // Checking Company 6 specifically as it had the mismatch
$rol_id = 6; // Auxiliar de Nómina

$employees = Usuario::where('id_rol', $rol_id)
    ->where(function($query) use ($id_empresa) {
        $query->whereHas('empresa', function($e) use ($id_empresa) {
            $e->where('empresa.id_empresa', $id_empresa);
        })->orWhereHas('contratos', function($c) use ($id_empresa) {
            $c->where('id_empresa', $id_empresa);
        });
    })->get();

echo "Company: $id_empresa | Rol: $rol_id | Count: " . $employees->count() . "\n";
foreach ($employees as $e) {
    echo "- DOC: {$e->doc} | Name: {$e->nombre_completo}\n";
}
