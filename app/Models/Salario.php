<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salario extends Model
{
    protected $table = 'salario';

    // Estados de Salario (Nómina)
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_LIQUIDADO = 'liquidado';
    public const ESTADO_PAGADO = 'pagado';

    protected $appends = [
        'total_devengos',
        'total_deducciones',
        'total_aportes',
        'salario_neto'
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'id_contrato');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo');
    }

    /* =======================
       ACCESSORS
    ======================= */

    public function getTotalDevengosAttribute()
    {
        $salarioBase = isset($this->attributes['salario_base'])
            ? (float) $this->attributes['salario_base']
            : (float) ($this->contrato->salario_base ?? 0);

        return $salarioBase
            + ($this->auxilio_transporte ?? 0)
            + ($this->valor_horas_extras_recargos ?? ($this->horas_extra ?? 0))
            + ($this->bonificaciones ?? 0)
            + ($this->comisiones ?? 0)
            + ($this->otros_devengos ?? 0);
    }

    public function getTotalDeduccionesAttribute()
    {
        return
            ($this->eps ?? 0) +
            ($this->afp ?? 0) +
            ($this->aporte_fp ?? 0) +
            ($this->retencion_fuente ?? 0) +
            ($this->embargo_fiscal ?? 0) +
            ($this->pension_voluntaria ?? 0);
    }

    public function getTotalAportesAttribute()
    {
        return ($this->seguridad_social ?? 0) + ($this->aporte_fp ?? 0);
    }

    public function getSalarioNetoAttribute()
    {
        return $this->total_devengos - $this->total_deducciones + ($this->total_novedades ?? 0);
    }
}
