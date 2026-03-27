<?php

use PhpOffice\PhpSpreadsheet\Style\Border;

$b = new Border();
$methods = get_class_methods($b);
echo "Métodos de Border:\n";
foreach($methods as $m) {
    if(stripos($m, 'border') !== false || stripos($m, 'set') !== false) {
        echo "  - $m\n";
    }
}
