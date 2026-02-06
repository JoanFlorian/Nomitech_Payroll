<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salario extends Model
{
    protected $table = 'salario';

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

    /* =======================
       ACCESSORS
    ======================= */

    public function getTotalDevengosAttribute()
    {
        return
            $this->salario_base +
            $this->auxilio_transporte +
            $this->horas_extra +
            $this->bonificaciones +
            $this->comisiones +
            $this->otros_devengos;
    }

    public function getTotalDeduccionesAttribute()
    {
        return
            ($this->salario_base * $this->eps / 100) +
            ($this->salario_base * $this->afp / 100);
    }

    public function getTotalAportesAttribute()
    {
        return $this->seguridad_social ?? 0;
    }

    public function getSalarioNetoAttribute()
    {
        return $this->total_devengos
             - $this->total_deducciones
             - $this->total_aportes;
    }
}
