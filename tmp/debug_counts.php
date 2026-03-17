<?php
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

$id_empresa = session('empresa_id') ?? 1; // Default to 1 for testing if session is empty

echo "--- ROLES IN DATABASE ---\n";
$roles = Rol::all();
foreach ($roles as $rol) {
    echo "ID: {$rol->id_rol} | Nombre: {$rol->nombre}\n";
}

echo "\n--- ALL USERS IN COMPANY $id_empresa ---\n";
$allUsers = Usuario::whereHas('empresa', function($q) use ($id_empresa) {
        $q->where('empresa.id_empresa', $id_empresa);
    })->get();

foreach ($allUsers as $u) {
    echo "DOC: {$u->doc} | Nombre: {$u->primer_nombre} {$u->primer_apellido} | Rol ID: {$u->id_rol} | Rol Name: " . ($u->rol->nombre ?? 'N/A') . "\n";
}
