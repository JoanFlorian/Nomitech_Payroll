<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Novedad extends Model
{
    use HasFactory;

    public const ESTADO_ACTIVA = 'activa';
    public const ESTADO_CERRADA = 'cerrada';

    protected $table = 'novedad';
    protected $primaryKey = 'id_novedad';
    protected $fillable = [
        'id_tipo_novedad',
        'id_salario',
        'id_periodo',
        'empleado_id',
        'estado',
        'tipo_novedad_nombre',
        'fecha',
        'fecha_inicio',
        'fecha_fin',
        'unidad_cantidad',
        'dias',
        'horas',
        'cantidad',
        'es_remunerado',
        'salario_base',
        'valor_calculado',
        'observaciones',
        'pago_manual',
        'valor_novedad',
        'pago',
        'tipo_movimiento',
        'afecta_ibc',
        'tipo_novedad_codigo',
        'tipo_licencia',
        'tipo_incapacidad',
        'certificado_medico',
        'afecta_nomina',
        'periodo_aplicado_id',
        'dias_restantes_rollover',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_remunerado' => 'boolean',
        'afecta_ibc' => 'boolean',
        'certificado_medico' => 'boolean',
        'afecta_nomina' => 'boolean',
    ];

    public function tipoNovedad()
    {
        return $this->belongsTo(TipoNovedad::class, 'id_tipo_novedad', 'id_tipo_novedad');
    }

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }

    public function periodoLiquidacion()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo', 'id_periodo');
    }

    /**
     * Verifica si el contrato tiene una incapacidad IGE o IRL activa en el periodo dado
     * que haya sido registrada en un periodo ANTERIOR (no en el periodo actual).
     *
     * Normativa colombiana:
     *  - IGE (Enfermedad General): la empresa cubre SOLO los 2 primeros días en el periodo de registro.
     *    En periodos siguientes, la EPS asume el pago → el empleado NO debe ser liquidado.
     *  - IRL (Incapacidad Riesgo Laboral): la ARL cubre desde el día 1.
     *    Si la incapacidad continúa en otro periodo → el empleado NO debe ser liquidado.
     *
     * @param  int    $idContrato   ID del contrato a verificar.
     * @param  int    $idPeriodo    ID del periodo que se está intentando liquidar.
     * @param  string $fechaInicio  Fecha inicio del periodo (YYYY-MM-DD).
     * @param  string $fechaFin     Fecha fin del periodo (YYYY-MM-DD).
     * @return bool   true si existe una incapacidad bloqueante.
     */
    public static function tieneIncapacidadActivaEnPeriodo(
        int $idContrato,
        int $idPeriodo,
        string $fechaInicio,
        string $fechaFin
    ): bool {
        return DB::table('novedad as n')
            ->join('salario as s', 's.id_salario', '=', 'n.id_salario')
            ->where('s.id_contrato', $idContrato)
            ->whereIn('n.tipo_novedad_codigo', ['IGE', 'IRL'])
            ->whereNotNull('n.fecha_inicio')
            ->whereNotNull('n.fecha_fin')
            ->whereDate('n.fecha_inicio', '<=', $fechaFin)
            ->whereDate('n.fecha_fin', '>=', $fechaInicio)
            ->where(function ($q) use ($idPeriodo) {
                // Solo bloquea si la incapacidad NO fue registrada en este mismo periodo.
                // En el periodo de registro, los primeros 2 días ya se liquidan correctamente.
                $q->where(function ($inner) use ($idPeriodo) {
                    // n.id_periodo está definido y apunta a otro periodo
                    $inner->whereNotNull('n.id_periodo')
                          ->where('n.id_periodo', '!=', $idPeriodo);
                })->orWhere(function ($inner) use ($idPeriodo) {
                    // n.id_periodo es NULL, se infiere del salario y apunta a otro periodo
                    $inner->whereNull('n.id_periodo')
                          ->where('s.id_periodo', '!=', $idPeriodo);
                });
            })
            ->exists();
    }
}
