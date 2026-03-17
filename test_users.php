<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

echo "Empresa ID in session: " . session('empresa_id') . "\n";

$users = Usuario::all();
foreach($users as $u) {
    $empresaIds = DB::table('usuario_empresa')->where('doc', $u->doc)->pluck('id_empresa')->toArray();
    echo "Doc: {$u->doc} | Nombre: {$u->primer_nombre} {$u->primer_apellido} | Empresas: " . implode(',', $empresaIds) . "\n";
}
