<?php
foreach(App\Models\Rol::all() as $r) {
    echo $r->id_rol . ': ' . $r->nombre . PHP_EOL;
}
