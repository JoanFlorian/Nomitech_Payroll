<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NominaEmployeeService
{
    public function buscarEmpleado($doc, $empresaId)
    {
        return DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('usuario.doc', $doc)
            ->where('contrato.id_empresa', $empresaId)
            ->select(
                'usuario.doc',
                DB::raw("CONCAT(
                    usuario.primer_nombre,' ',
                    IFNULL(usuario.otros_nombres,''),' ',
                    usuario.primer_apellido,' ',
                    IFNULL(usuario.segundo_apellido,'')
                ) as nombre"),
                'usuario.telefono',
                'contrato.salario_base',
                'contrato.id_contrato'
            )
            ->first();
    }
}