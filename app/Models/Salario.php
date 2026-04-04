<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Salario extends Model
{
    protected $table = 'salario';
    protected $primaryKey = 'id_salario';
    protected $guarded = [];

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

    protected ?array $cachedNovedadesTotals = null;

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'id_contrato');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo');
    }

    public function novedades()
    {
        return $this->hasMany(Novedad::class, 'id_salario', 'id_salario');
    }

    /* =======================
       ACCESSORS
    ======================= */

    public function getTotalDevengosAttribute()
    {
        $dias = (int) ($this->dias_trabajados ?? ($this->dias_a_trabajar ?? 30));
        $salarioMensual = (float) ($this->contrato->salario_base ?? 0);
        $salarioBase = ($salarioMensual / 30) * $dias;

        $novedades = $this->resolveNovedadesTotals();

        $integratedBenefits = (float)($this->prestaciones_sociales ?? 0);
        
        // Solo si no hay dato guardado (ej. en medio del proceso de liquidación) 
        // o si es una nómina normal donde no se guardó el desglose prestacional
        if ($integratedBenefits <= 0) {
            $integratedBenefits = DB::table('benefit_ledger')
                ->where('contract_id', $this->id_contrato)
                ->where('payroll_period_id', $this->id_periodo)
                ->where('movement_type', 'scheduled_payment')
                ->where('status', 'pending_payroll')
                ->sum('amount');
        }

        return $salarioBase
            + ($this->auxilio_transporte ?? 0)
            + ($this->valor_horas_extras_recargos ?? ($this->horas_extra ?? 0))
            + ($this->bonificaciones ?? 0)
            + ($this->comisiones ?? 0)
            + ($this->otros_devengos ?? 0)
            + ($novedades['devengado'] ?? 0)
            + abs((float) $integratedBenefits);
    }

    public function getTotalDeduccionesAttribute()
    {
        $novedades = $this->resolveNovedadesTotals();

        return
            ($this->eps ?? 0) +
            ($this->afp ?? 0) +
            ($this->aporte_fp ?? 0) +
            ($this->retencion_fuente ?? 0) +
            ($this->embargo_fiscal ?? 0) +
            ($this->pension_voluntaria ?? 0) +
            ($novedades['deduccion'] ?? 0);
    }

    public function getTotalAportesAttribute()
    {
        return ($this->seguridad_social ?? 0) + ($this->aporte_fp ?? 0);
    }

    public function getSalarioNetoAttribute()
    {
        return $this->total_devengos - $this->total_deducciones;
    }

    private function resolveNovedadesTotals(): array
    {
        if ($this->cachedNovedadesTotals !== null) {
            return $this->cachedNovedadesTotals;
        }

        $devAttr = $this->attributes['total_novedades_devengado'] ?? null;
        $dedAttr = $this->attributes['total_novedades_deduccion'] ?? null;

        if ($devAttr !== null || $dedAttr !== null) {
            $this->cachedNovedadesTotals = [
                'devengado' => (float) ($devAttr ?? 0),
                'deduccion' => (float) ($dedAttr ?? 0),
            ];

            return $this->cachedNovedadesTotals;
        }

        if (!$this->getKey()) {
            $this->cachedNovedadesTotals = ['devengado' => 0.0, 'deduccion' => 0.0];

            return $this->cachedNovedadesTotals;
        }

        $resumen = $this->novedades()
            ->selectRaw('COALESCE(SUM(CASE WHEN valor_calculado > 0 THEN valor_calculado ELSE 0 END), 0) as total_devengado')
            ->selectRaw('COALESCE(SUM(CASE WHEN valor_calculado < 0 THEN ABS(valor_calculado) ELSE 0 END), 0) as total_deduccion')
            ->first();

        $this->cachedNovedadesTotals = [
            'devengado' => (float) ($resumen->total_devengado ?? 0),
            'deduccion' => (float) ($resumen->total_deduccion ?? 0),
        ];

        return $this->cachedNovedadesTotals;
    }
}
