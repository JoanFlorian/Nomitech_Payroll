<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitLedger extends Model
{
    protected $table = 'benefit_ledger';
    protected $primaryKey = 'id';

    /* ── Benefit Types ── */
    public const TYPE_PRIMA = 'prima';
    public const TYPE_CESANTIAS = 'cesantias';
    public const TYPE_INTERESES_CESANTIAS = 'intereses_cesantias';
    public const TYPE_VACACIONES = 'vacaciones';

    /* ── Movement Types ── */
    public const MOVEMENT_ACCRUAL = 'accrual';
    public const MOVEMENT_PAYMENT = 'payment';
    public const MOVEMENT_ADJUSTMENT = 'adjustment';
    public const MOVEMENT_INITIAL = 'initial';

    /* ── Sources ── */
    public const SOURCE_PAYROLL = 'payroll';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_LIQUIDATION = 'liquidation';
    public const SOURCE_MIGRATION = 'migration';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'contract_id',
        'benefit_type',
        'movement_type',
        'amount',
        'period_id',
        'source',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'tenant_id', 'id_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'employee_id', 'doc');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contract_id', 'id_contrato');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'period_id', 'id_periodo');
    }

    /* ── Scopes ── */

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForEmployee($query, $doc)
    {
        return $query->where('employee_id', $doc);
    }

    public function scopeOfType($query, string $benefitType)
    {
        return $query->where('benefit_type', $benefitType);
    }

    /* ── Labels for UI ── */

    public static function benefitTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_PRIMA => 'Prima',
            self::TYPE_CESANTIAS => 'Cesantías',
            self::TYPE_INTERESES_CESANTIAS => 'Intereses de Cesantías',
            self::TYPE_VACACIONES => 'Vacaciones',
            default => $type,
        };
    }

    public static function movementTypeLabel(string $type): string
    {
        return match ($type) {
            self::MOVEMENT_ACCRUAL => 'Causación',
            self::MOVEMENT_PAYMENT => 'Pago',
            self::MOVEMENT_ADJUSTMENT => 'Ajuste',
            self::MOVEMENT_INITIAL => 'Saldo Inicial',
            default => $type,
        };
    }
}
