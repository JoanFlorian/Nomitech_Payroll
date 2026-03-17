<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

$auxiliars = Usuario::whereHas('roles', function($q){
    $q->where('name', 'Auxiliar de Nómina');
})->get();

if ($auxiliars->isEmpty()) {
    echo "NO_AUXILIARS_FOUND\n";
    exit;
}

foreach ($auxiliars as $auxiliar) {
    echo "--- USER: " . $auxiliar->doc . " (" . $auxiliar->nombre_completo . ") ---\n";
    
    // Check usuario_empresa
    $ue = DB::table('usuario_empresa')->where('doc', $auxiliar->doc)->get();
    foreach($ue as $row) {
        echo "  - UE_LINK: Empresa ID " . $row->id_empresa . "\n";
    }
    
    // Check contracts
    $contracts = $auxiliar->contratos()->get();
    foreach($contracts as $c) {
        echo "  - CONTRACT_LINK: ID " . $c->id_contrato . " Empresa ID " . $c->id_empresa . "\n";
    }
    
    // Check roles scoped to company
    $roles = DB::table('user_roles')->where('user_id', $auxiliar->doc)->get();
    foreach($roles as $r) {
        $role = DB::table('roles')->where('id', $r->role_id)->first();
        echo "  - ROLE_LINK: " . ($role->name ?? 'Unknown') . " (ID " . $r->role_id . ") for Company " . ($role->company_id ?? 'NULL') . "\n";
    }
}
