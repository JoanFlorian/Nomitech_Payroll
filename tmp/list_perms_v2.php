<?php
use App\Models\Permission;
foreach(Permission::all() as $p) {
    echo "PERM:{$p->name}|{$p->module}\n";
}
