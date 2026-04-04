<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = \App\Models\Salario::find(581);
echo "ID Salario: " . $s->id_salario . "\n";
echo "Dias a Trabajar (Base): " . $s->dias_a_trabajar . "\n";
echo "Dias Trabajados (Effective): " . $s->dias_trabajados . "\n";
echo "Dias Ausencia: " . $s->dias_ausencia . "\n";
echo "Sueldo Base: " . $s->sueldo_base . "\n";
echo "Total Devengado: " . $s->total_devengado . "\n";
