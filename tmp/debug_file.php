<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

$output = "";
$id_empresa = 1; // Assuming company 1 for now, but I should probably check session or just check all

$output .= "--- ROLES ---\n";
foreach (Rol::all() as $r) {
    $output .= "ID: {$r->id_rol} | {$r->nombre}\n";
}

$output .= "\n--- ALL USERS IN DB ---\n";
$allUsers = Usuario::all();
foreach ($allUsers as $u) {
    $companies = $u->empresa->pluck('id_empresa')->implode(',');
    $output .= "DOC: {$u->doc} | Name: {$u->nombre_completo} | Rol: {$u->id_rol} (" . ($u->rol->nombre ?? 'N/A') . ") | Companies: [$companies]\n";
}

file_put_contents('tmp/debug_results.txt', $output);
echo "Done. Results in tmp/debug_results.txt\n";
