<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;

$output = "--- USERS WITH CONTRACTS VS USERS IN USUARIO_EMPRESA ---\n\n";

$allUsers = Usuario::all();
foreach ($allUsers as $u) {
    // Linked companies in pivot table
    $linkedCompanies = DB::table('usuario_empresa')->where('doc', $u->doc)->pluck('id_empresa')->toArray();
    
    // Companies in contracts table
    $contractCompanies = Contrato::where('doc', $u->doc)->distinct('id_empresa')->pluck('id_empresa')->toArray();
    
    $missingInPivot = array_diff($contractCompanies, $linkedCompanies);
    
    if (count($missingInPivot) > 0 || count($linkedCompanies) > 0 || count($contractCompanies) > 0) {
        $output .= "DOC: {$u->doc} | Rol: {$u->id_rol} | Name: {$u->nombre_completo}\n";
        $output .= "  Pivot Links: [" . implode(',', $linkedCompanies) . "]\n";
        $output .= "  Contract Co: [" . implode(',', $contractCompanies) . "]\n";
        if (count($missingInPivot) > 0) {
            $output .= "  !!! MISSING IN PIVOT: [" . implode(',', $missingInPivot) . "]\n";
        }
        $output .= "\n";
    }
}

file_put_contents('tmp/debug_pivot.txt', $output);
echo "Done. Results in tmp/debug_pivot.txt\n";
