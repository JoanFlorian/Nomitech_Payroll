<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\Usuario::find('1111110');
if ($u) {
    echo "IS_OWNER: " . ($u->is_owner ? 'YES' : 'NO') . "\n";
} else {
    echo "USER_NOT_FOUND\n";
}
