<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollParameter extends Model
{
    protected $table = 'payroll_parameters';

    protected $fillable = [
        'smmlv',
        'auxilio_transporte',
        'auxilio_transporte_tope',
        'eps_employee',
        'pension_employee',
        'fondo_solidaridad',
        'eps_employer',
        'pension_employer',
        'arl_riesgo_1',
        'caja_compensacion',
        'fondo_solidaridad_threshold',
        'horas_mes',
    ];

    protected $casts = [
        'smmlv' => 'decimal:2',
        'auxilio_transporte' => 'decimal:2',
        'auxilio_transporte_tope' => 'integer',
        'eps_employee' => 'decimal:4',
        'pension_employee' => 'decimal:4',
        'fondo_solidaridad' => 'decimal:4',
        'eps_employer' => 'decimal:4',
        'pension_employer' => 'decimal:4',
        'arl_riesgo_1' => 'decimal:4',
        'caja_compensacion' => 'decimal:4',
        'fondo_solidaridad_threshold' => 'integer',
        'horas_mes' => 'integer',
    ];
}
