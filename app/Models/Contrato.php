<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contrato extends Model
{
    protected $table = 'contrato';
    protected $primaryKey = 'id_contrato';

    // ── Estados del contrato ──
    public const ESTADO_PROGRAMADO  = 'PROGRAMADO';
    public const ESTADO_ACTIVO      = 'ACTIVO';
    public const ESTADO_POR_VENCER  = 'POR_VENCER';
    public const ESTADO_VENCIDO     = 'VENCIDO';
    public const ESTADO_TERMINADO   = 'TERMINADO';

    // ── Constante de periodo de tolerancia para continuidad ──
    public const CONTINUIDAD_DIAS_TOLERANCIA = 30;

    // ── Periodo de Gracia para acceso post-liquidación ──
    public const GRACE_PERIOD_DAYS = 3;

    // ── Aliases de compatibilidad (facilitan la migración gradual) ──
    public const ESTADO_LABORAL_ACTIVO    = 'ACTIVO';
    public const ESTADO_LABORAL_TERMINADO = 'TERMINADO';
    public const ESTADO_NOMINA_PENDIENTE  = 'VENCIDO';
    public const ESTADO_NOMINA_LIQUIDADO  = 'TERMINADO';

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
        'estado',
        'fecha_liquidacion_final',
        'salario_final_pagado_at',
        'prestaciones_liquidadas_at',
        'cesantias_transferidas_at',
        'vacaciones_liquidadas_at',
    ];

    protected function casts(): array
    {
        return [
            'activo'     => 'boolean',
            'alto_riesgo' => 'boolean',
            'fecha_inicio' => 'date',
            'fecha_fin'   => 'date',
            'fecha_liquidacion_final'    => 'datetime',
            'salario_final_pagado_at'    => 'datetime',
            'prestaciones_liquidadas_at' => 'datetime',
            'cesantias_transferidas_at'  => 'datetime',
            'vacaciones_liquidadas_at'   => 'datetime',
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ESTADO DINÁMICO (calculado a partir de fechas)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Calcula el estado del contrato basado en las fechas actuales.
     * Si el contrato fue explícitamente marcado TERMINADO, respeta ese valor.
     */
    public function getEstadoDinamicoAttribute(): string
    {
        // Si ya fue marcado como terminado manualmente, respetar
        if ($this->estado === self::ESTADO_TERMINADO) {
            return self::ESTADO_TERMINADO;
        }

        $hoy = Carbon::today();

        // PROGRAMADO: aún no inicia
        if ($this->fecha_inicio && $hoy->lt($this->fecha_inicio)) {
            return self::ESTADO_PROGRAMADO;
        }

        // Sin fecha_fin = contrato indefinido → siempre activo
        if (!$this->fecha_fin) {
            return self::ESTADO_ACTIVO;
        }

        // POR_VENCER: activo pero a ≤ 30 días de terminar
        if ($hoy->lte($this->fecha_fin)) {
            $diasRestantes = $hoy->diffInDays($this->fecha_fin, false);
            return $diasRestantes <= self::CONTINUIDAD_DIAS_TOLERANCIA
                ? self::ESTADO_POR_VENCER
                : self::ESTADO_ACTIVO;
        }

        // Pasó fecha_fin → VENCIDO (tiene pendientes) o TERMINADO (todo liquidado)
        return $this->tienePendientes()
            ? self::ESTADO_VENCIDO
            : self::ESTADO_TERMINADO;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PENDIENTES DE LIQUIDACIÓN
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Verifica si el contrato tiene algún pendiente de liquidación.
     */
    public function tienePendientes(): bool
    {
        return is_null($this->salario_final_pagado_at)
            || is_null($this->prestaciones_liquidadas_at)
            || is_null($this->cesantias_transferidas_at)
            || is_null($this->vacaciones_liquidadas_at);
    }

    /**
     * Retorna la lista textual de alertas de liquidación pendientes.
     */
    public function getPendientesAttribute(): array
    {
        $pendientes = [];

        if (is_null($this->salario_final_pagado_at)) {
            $pendientes[] = 'Falta pagar salario final';
        }
        if (is_null($this->prestaciones_liquidadas_at)) {
            $pendientes[] = 'Falta liquidar prestaciones sociales';
        }
        if (is_null($this->cesantias_transferidas_at)) {
            $pendientes[] = 'Falta transferir cesantías al fondo';
        }
        if (is_null($this->vacaciones_liquidadas_at)) {
            $pendientes[] = 'Falta liquidar vacaciones';
        }

        return $pendientes;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  HELPERS DE ESTADO
    // ═══════════════════════════════════════════════════════════════════

    /**
     * ¿El contrato está actualmente vigente?
     */
    public function estaVigente(): bool
    {
        return in_array($this->estado_dinamico, [
            self::ESTADO_ACTIVO,
            self::ESTADO_POR_VENCER,
            self::ESTADO_PROGRAMADO,
        ]);
    }

    /**
     * Sincroniza el campo `estado` de la BD con el estado dinámico calculado.
     */
    public function syncEstado(): self
    {
        $estadoCalculado = $this->estado_dinamico;

        if ($this->estado !== $estadoCalculado) {
            $this->estado = $estadoCalculado;
            $this->saveQuietly();
        }

        return $this;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RELACIONES
    // ═══════════════════════════════════════════════════════════════════

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

