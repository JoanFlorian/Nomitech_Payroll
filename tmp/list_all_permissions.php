<?php
foreach(App\Models\Permission::all() as $p) {
    echo $p->name . "|" . $p->module . PHP_EOL;
}
