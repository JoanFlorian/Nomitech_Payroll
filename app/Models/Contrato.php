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
        'id_tipo_trabajador',
        'id_sub_tipo_trabajador',
        'id_forma_pago',
        'id_metodo_pago',
        'id_arl',
        'id_eps',
        'id_afp',
        'fecha_inicio',
        'fecha_fin',
        'salario_base',
        'salario',
        'activo',
        'alto_riesgo',
        'nivel_riesgo',
        'horas_diarias',
        'codigo_interno',
        'tipo_cuenta',
        'numero_cuenta'
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'alto_riesgo' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'doc', 'doc');
    }

    public function tipoContrato()
    {
        return $this->belongsTo(TipoContrato::class, 'id_tipo_contrato', 'id_tipo_contrato');
    }

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_contrato');
    }
}
