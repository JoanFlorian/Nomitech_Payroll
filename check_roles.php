<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Rol;

$roles = Rol::all();
$output = "";
foreach($roles as $r) {
    $output .= "ID: " . $r->id_rol . " | Name: [" . $r->nombre . "]\n";
}
file_put_contents('c:/xampp/htdocs/Nomitech_Payroll/roles_list.txt', $output);
echo "Check roles_list.txt\n";
