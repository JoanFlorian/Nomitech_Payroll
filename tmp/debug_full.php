<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

$id_empresa = 1; // Testing with company 1

echo "--- ROLES ---\n";
foreach (Rol::all() as $r) {
    echo "ID: {$r->id_rol} | {$r->nombre}\n";
}

echo "\n--- USERS IN COMPANY $id_empresa ---\n";
$users = Usuario::whereHas('empresa', function($q) use ($id_empresa) {
    $q->where('empresa.id_empresa', $id_empresa);
})->get();

foreach ($users as $u) {
    echo "DOC: {$u->doc} | Name: {$u->nombre_completo} | Rol: {$u->id_rol} (" . ($u->rol->nombre ?? 'N/A') . ")\n";
}

$counts = Usuario::whereHas('empresa', function($q) use ($id_empresa) {
    $q->where('empresa.id_empresa', $id_empresa);
})->select('id_rol', DB::raw('count(*) as total'))->groupBy('id_rol')->get();

echo "\n--- SUMMARY COUNTS ---\n";
foreach ($counts as $c) {
    echo "Rol {$c->id_rol}: {$c->total}\n";
}
