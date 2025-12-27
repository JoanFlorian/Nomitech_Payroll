<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salario extends Model
{
    use HasFactory;

    protected $table = 'salario';
    protected $primaryKey = 'id_salario';
    protected $fillable = [
        'id_contrato',
        'id_periodo',
        'id_estado',
        'auxilio_transporte',
        'horas_extra',
        'bonificaciones',
        'comisiones',
        'otros_devengos',
        'arl',
        'eps',
        'afp',
        'seguridad_social',
        'aporte_fp',
        'retencion_fuente',
        'embargo_fiscal',
        'pension_voluntaria',
        'dias_a_trabajar',
        'horas_mensual',
        'fecha_pago'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo', 'id_periodo');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    public function novedades()
    {
        return $this->hasMany(Novedad::class, 'id_salario', 'id_salario');
    }

    public function horasRecargoExtra()
    {
        return $this->hasMany(HoraRecargoExtra::class, 'id_salario', 'id_salario');
    }

    public function nominaElectronica()
    {
        return $this->hasOne(NominaElectronica::class, 'id_salario', 'id_salario');
    }
}
