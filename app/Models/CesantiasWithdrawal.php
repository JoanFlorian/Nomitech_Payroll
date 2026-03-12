<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tracks Cesantías withdrawal requests.
 *
 * Domain rules:
 *  - If cesantías have NOT been deposited to the fund → payment_origin = 'company'
 *    (employer pays employee directly, ledger movement with destination = employee)
 *  - If cesantías have ALREADY been deposited → payment_origin = 'fund'
 *    (employer only authorizes, generates certificate, no balance change)
 */
class CesantiasWithdrawal extends Model
{
    protected $table = 'cesantias_withdrawals';

    /* ── Reason Constants ── */
    public const REASON_HOUSING = 'housing';
    public const REASON_EDUCATION = 'education';

    /* ── Payment Origin ── */
    public const ORIGIN_COMPANY = 'company';
    public const ORIGIN_FUND = 'fund';

    /* ── Status ── */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'employee_id',
        'company_id',
        'amount',
        'reason',
        'payment_origin',
        'status',
        'certificate_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /* ── Relationships ── */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'employee_id', 'doc');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'company_id', 'id_empresa');
    }

    /* ── Labels ── */

    public static function reasonLabel(string $reason): string
    {
        return match ($reason) {
            self::REASON_HOUSING => 'Vivienda',
            self::REASON_EDUCATION => 'Educación',
            default => $reason,
        };
    }

    public static function originLabel(string $origin): string
    {
        return match ($origin) {
            self::ORIGIN_COMPANY => 'Empresa (pago directo)',
            self::ORIGIN_FUND => 'Fondo (autorización)',
            default => $origin,
        };
    }
}
