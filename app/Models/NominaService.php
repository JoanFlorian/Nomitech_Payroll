<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class NominaService
{
    public function liquidar(Salario $salario)
    {
        $devengos =
            $salario->salario_base +
            $salario->auxilio_transporte +
            $salario->comisiones +
            $salario->bonificaciones +
            $salario->otros_devengos;

        $deducciones =
            ($salario->salario_base * $salario->eps / 100) +
            ($salario->salario_base * $salario->afp / 100) +
            $salario->otras_deducciones;

        return [
            'devengos' => $devengos,
            'deducciones' => $deducciones,
            'neto' => $devengos - $deducciones
        ];
    }
}
