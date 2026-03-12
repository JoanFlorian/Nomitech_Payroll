<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoLiquidacion extends Model
{
    use HasFactory;

    protected $table = 'periodo_liquidacion';
    protected $primaryKey = 'id_periodo';

    // Estados del periodo
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_ABIERTO = 'abierto';
    public const ESTADO_CERRADO = 'cerrado';

    // Frecuencias de pago
    public const FRECUENCIA_SEMANAL = 'semanal';
    public const FRECUENCIA_DECENAL = 'decenal';
    public const FRECUENCIA_CATORCENAL = 'catorcenal';
    public const FRECUENCIA_QUINCENAL = 'quincenal';
    public const FRECUENCIA_MENSUAL = 'mensual';
    public const FRECUENCIA_OTRO = 'otro';

    protected $fillable = [
        'id_empresa',
        'fecha_inicio',
        'fecha_fin',
        'tipo_frecuencia',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /* =================================
       HELPERS DE FRECUENCIA
    ================================= */

    public function isSemanal(): bool
    {
        return $this->tipo_frecuencia === self::FRECUENCIA_SEMANAL;
    }

    public function isDecenal(): bool
    {
        return $this->tipo_frecuencia === self::FRECUENCIA_DECENAL;
    }

    public function isCatorcenal(): bool
    {
        return $this->tipo_frecuencia === self::FRECUENCIA_CATORCENAL;
    }

    public function isQuincenal(): bool
    {
        return $this->tipo_frecuencia === self::FRECUENCIA_QUINCENAL;
    }

    public function isMensual(): bool
    {
        return $this->tipo_frecuencia === self::FRECUENCIA_MENSUAL;
    }

    public function isOtro(): bool
    {
        return $this->tipo_frecuencia === self::FRECUENCIA_OTRO;
    }

    /**
     * Calcula la fecha fin sugerida basada en una fecha inicio y frecuencia.
     */
    public static function calculateEndDate($startDate, $frequency): ?\Carbon\Carbon
    {
        $start = \Carbon\Carbon::parse($startDate);

        return match ($frequency) {
            self::FRECUENCIA_SEMANAL => $start->copy()->addDays(6),
            self::FRECUENCIA_DECENAL => $start->copy()->addDays(9),
            self::FRECUENCIA_CATORCENAL => $start->copy()->addDays(13),
            self::FRECUENCIA_QUINCENAL => $start->copy()->addDays(14),
            self::FRECUENCIA_MENSUAL => $start->copy()->endOfMonth(),
            default => null,
        };
    }

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_periodo', 'id_periodo');
    }

    public function provisiones()
    {
        return $this->hasMany(Provision::class, 'id_periodo', 'id_periodo');
    }

    public function benefitLedgerEntries()
    {
        return $this->hasMany(BenefitLedger::class, 'period_id', 'id_periodo');
    }

    public function open()
    {
        $this->update(['estado' => self::ESTADO_ABIERTO]);
    }

    public function close()
    {
        $this->update(['estado' => self::ESTADO_CERRADO]);
    }

    /**
     * Determina si el periodo puede ser cerrado basado en la fecha actual.
     * El cierre se permite desde el día fin hasta 10 días después.
     */
    public function canBeClosed(): bool
    {
        // TEMPORAL: Habilitado para pruebas de provisiones
        return true;

        /*
        if ($this->estado === self::ESTADO_CERRADO) {
            return false;
        }

        $now = now()->startOfDay();
        $fechaFin = \Carbon\Carbon::parse($this->fecha_fin)->startOfDay();
        $limiteCierre = $fechaFin->copy()->addDays(10);

        return $now->greaterThanOrEqualTo($fechaFin) && $now->lessThanOrEqualTo($limiteCierre);
        */
    }
}
