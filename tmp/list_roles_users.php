<?php
foreach(App\Models\Rol::all() as $r) {
    $count = App\Models\Usuario::where('id_rol', $r->id_rol)->count();
    echo $r->id_rol . ': ' . $r->nombre . ' (Users: ' . $count . ')' . PHP_EOL;
}
