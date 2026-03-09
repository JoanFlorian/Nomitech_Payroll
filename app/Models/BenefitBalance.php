<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BenefitBalance extends Model
{
    protected $table = 'benefit_balance';
    protected $primaryKey = 'id';

    public $timestamps = false; // only updated_at, managed manually

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'prima_balance',
        'cesantias_balance',
        'intereses_balance',
        'vacaciones_balance',
    ];

    protected $casts = [
        'prima_balance' => 'decimal:2',
        'cesantias_balance' => 'decimal:2',
        'intereses_balance' => 'decimal:2',
        'vacaciones_balance' => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'employee_id', 'doc');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'tenant_id', 'id_empresa');
    }

    /* ── Incremental Update ── */

    /**
     * Incrementally update the balance for a specific benefit type.
     * This is the PRIMARY method for balance updates.
     */
    public function applyMovement(string $benefitType, float $amount): void
    {
        $column = self::balanceColumn($benefitType);
        $this->$column = bcadd((string) $this->$column, (string) $amount, 2);
        $this->updated_at = now();
        $this->save();
    }

    /**
     * Map benefit_type to balance column name.
     */
    public static function balanceColumn(string $benefitType): string
    {
        return match ($benefitType) {
            BenefitLedger::TYPE_PRIMA => 'prima_balance',
            BenefitLedger::TYPE_CESANTIAS => 'cesantias_balance',
            BenefitLedger::TYPE_INTERESES_CESANTIAS => 'intereses_balance',
            BenefitLedger::TYPE_VACACIONES => 'vacaciones_balance',
            default => throw new \InvalidArgumentException("Unknown benefit type: {$benefitType}"),
        };
    }

    /* ── Recovery Tool ── */

    /**
     * Recalculate balance from the full ledger history.
     * USE ONLY for recovery/diagnostics, NOT for normal operations.
     */
    public function recalculateFromLedger(): void
    {
        $sums = BenefitLedger::where('tenant_id', $this->tenant_id)
            ->where('employee_id', $this->employee_id)
            ->selectRaw("
                SUM(CASE WHEN benefit_type = 'prima' THEN amount ELSE 0 END) as prima,
                SUM(CASE WHEN benefit_type = 'cesantias' THEN amount ELSE 0 END) as cesantias,
                SUM(CASE WHEN benefit_type = 'intereses_cesantias' THEN amount ELSE 0 END) as intereses,
                SUM(CASE WHEN benefit_type = 'vacaciones' THEN amount ELSE 0 END) as vacaciones
            ")
            ->first();

        $this->update([
            'prima_balance' => $sums->prima ?? 0,
            'cesantias_balance' => $sums->cesantias ?? 0,
            'intereses_balance' => $sums->intereses ?? 0,
            'vacaciones_balance' => $sums->vacaciones ?? 0,
        ]);
    }

    /* ── Helper ── */

    /**
     * Find or create a balance row for the given employee/tenant.
     */
    public static function findOrCreateFor(string $employeeId, int $tenantId): self
    {
        return self::firstOrCreate(
            ['employee_id' => $employeeId, 'tenant_id' => $tenantId],
            [
                'prima_balance' => 0,
                'cesantias_balance' => 0,
                'intereses_balance' => 0,
                'vacaciones_balance' => 0,
            ]
        );
    }
}
