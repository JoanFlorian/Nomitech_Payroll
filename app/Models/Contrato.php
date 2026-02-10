<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Contrato extends Model
{
    protected $table = 'contrato';
    protected $primaryKey = 'id_contrato';

    protected $fillable = [
        'doc',
        'id_empresa',
        'id_tipo_contrato',
        'id_sub_tipo_trabajador',
        'id_forma_pago',
        'id_metodo_pago',
        'fecha_inicio',
        'fecha_fin',
        'salario_base',
        'activo'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'doc', 'doc');
    }

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_contrato');
    }
}
