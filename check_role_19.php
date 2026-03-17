<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = \App\Models\Role::find(19);
if ($role) {
    echo "ROLE_NAME:" . $role->name . "\n";
    echo "PERMISSIONS:" . implode(',', $role->permissions->pluck('name')->toArray()) . "\n";
} else {
    echo "ROLE_19_NOT_FOUND\n";
}
