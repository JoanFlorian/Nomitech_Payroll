<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    use HasFactory;

    protected $table = 'novedad';
    protected $primaryKey = 'id_novedad';
    protected $fillable = [
        'id_tipo_novedad',
        'id_salario',
        'id_periodo',
        'empleado_id',
        'tipo_novedad_nombre',
        'fecha',
        'fecha_inicio',
        'fecha_fin',
        'unidad_cantidad',
        'dias',
        'horas',
        'cantidad',
        'es_remunerado',
        'salario_base',
        'valor_calculado',
        'observaciones',
        'pago_manual',
        'valor_novedad',
        'pago',
        'tipo_movimiento',
        'afecta_ibc',
        'tipo_novedad_codigo',
        'tipo_licencia',
        'tipo_incapacidad',
        'certificado_medico',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_remunerado' => 'boolean',
        'afecta_ibc' => 'boolean',
        'certificado_medico' => 'boolean',
    ];

    public function tipoNovedad()
    {
        return $this->belongsTo(TipoNovedad::class, 'id_tipo_novedad', 'id_tipo_novedad');
    }

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }

    public function periodoLiquidacion()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo', 'id_periodo');
    }
}
