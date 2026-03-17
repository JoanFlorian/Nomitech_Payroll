<?php

foreach(['301032011', '3200121010', '1111110', '333333'] as $doc) {
    $c = \App\Models\Contrato::with(['formaPago', 'metodoPago'])->where('doc', $doc)->first();
    if($c) {
        $forma = $c->formaPago ? $c->formaPago->nombre : 'null';
        $metodo = $c->metodoPago ? $c->metodoPago->nombre : 'null';
        echo "Doc: $doc - Forma: {$c->id_forma_pago} ($forma) - Metodo: {$c->id_metodo_pago} ($metodo)\n";
    } else {
        echo "Doc: $doc - Contrato no encontrado\n";
    }
}
