<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

$doc = '1111110';
$id_empresa = 6;

$user = Usuario::find($doc);
$overrides = DB::table('user_permissions')
    ->where('user_id', $doc)
    ->where('company_id', $id_empresa)
    ->get();

echo "USER: " . ($user->nombre_completo ?? 'Unknown') . "\n";
echo "OVERRIDES_COUNT: " . $overrides->count() . "\n";
foreach($overrides as $o) {
    $p = DB::table('permissions')->where('id', $o->permission_id)->first();
    echo "  - PERM: " . ($p->name ?? 'Unknown') . " ACTIVE: " . $o->active . "\n";
}
