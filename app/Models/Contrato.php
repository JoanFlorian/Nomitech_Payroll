<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Contrato extends Model
{
    protected $table = 'contrato';
    protected $primaryKey = 'id_contrato';

    // Estados Laborales
    public const ESTADO_LABORAL_ACTIVO = 1;
    public const ESTADO_LABORAL_TERMINADO = 2;

    // Estados de Nómina
    public const ESTADO_NOMINA_PENDIENTE = 1;
    public const ESTADO_NOMINA_LIQUIDADO = 2;

    // Periodo de Gracia
    public const GRACE_PERIOD_DAYS = 3;

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($contrato) {
            // If estado_nomina changes to LIQUIDADO, automatically set the date
            if (
                $contrato->isDirty('estado_nomina') &&
                (int) $contrato->estado_nomina === self::ESTADO_NOMINA_LIQUIDADO
            ) {
                $contrato->fecha_liquidacion_final = now();
            }
        });
    }

    protected $fillable = [
        'doc',
        'id_empresa',
        'id_tipo_contrato',
        'id_tipo_trabajador',
        'id_sub_tipo_trabajador',
        'id_forma_pago',
        'id_metodo_pago',
        'id_arl',
        'id_eps',
        'id_afp',
        'fecha_inicio',
        'fecha_fin',
        'salario_base',
        'salario',
        'activo',
        'alto_riesgo',
        'nivel_riesgo',
        'horas_diarias',
        'codigo_interno',
        'tipo_cuenta',
        'numero_cuenta',
        'estado_laboral',
        'estado_nomina',
        'fecha_liquidacion_final'
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'alto_riesgo' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'estado_laboral' => 'integer',
            'estado_nomina' => 'integer',
            'fecha_liquidacion_final' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'doc', 'doc');
    }

    public function tipoContrato()
    {
        return $this->belongsTo(TipoContrato::class, 'id_tipo_contrato', 'id_tipo_contrato');
    }

    public function tipoTrabajador()
    {
        return $this->belongsTo(TipoTrabajador::class, 'id_tipo_trabajador', 'id_tipo_trabajador');
    }

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_contrato');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Relación con la cuenta bancaria activa.
     */
    public function cuentaActiva()
    {
        return $this->hasOne(Cuenta::class, 'id_contrato', 'id_contrato')
            ->where('activo', true);
    }

    public function benefitLedgerEntries()
    {
        return $this->hasMany(BenefitLedger::class, 'contract_id', 'id_contrato');
    }

    public function benefitBalance()
    {
        return $this->hasOne(BenefitBalance::class, 'employee_id', 'doc');
    }
}
