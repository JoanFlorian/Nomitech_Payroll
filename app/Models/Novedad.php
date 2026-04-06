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
        'soporte_medico_path',
        'soporte_medico_original_name',
        'soporte_medico_mime',
        'soporte_medico_size',
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
        'soporte_medico_size' => 'integer',
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

    /**
     * Obtiene la novedad de LMAT/LPAT activa para un contrato (si existe).
     * Busca la novedad más reciente que aún está activa.
     * 
     * @param  int $idContrato ID del contrato
     * @return Novedad|null
     */
    public static function obtenerLmatActivaParaContrato(int $idContrato): ?Novedad
    {
        return self::query()
            ->join('salario as s', 's.id_salario', '=', 'novedad.id_salario')
            ->where('s.id_contrato', $idContrato)
            ->whereIn('novedad.tipo_novedad_codigo', ['LMAT', 'LPAT'])
            ->where('novedad.estado', self::ESTADO_ACTIVA)
            ->where(function ($q) {
                // Incluir si fecha_fin es NULL o si es posterior a hoy
                $q->whereNull('novedad.fecha_fin')
                  ->orWhere('novedad.fecha_fin', '>=', now()->toDateString());
            })
            ->select('novedad.*')
            ->orderByDesc('novedad.fecha_inicio')
            ->first();
    }

    /**
     * Obtiene el estado detallado de la LMAT activa para un contrato.
     * Calcula dinámicamente los días restantes sin modificar la BD.
     * 
     * Incluye: tipo, días totales, días ya pagados, días restantes, fechas, etc.
     * 
     * @param  int $idContrato ID del contrato
     * @return array|null
     */
    public static function obtenerEstadoLmat(int $idContrato): ?array
    {
        $lmat = self::obtenerLmatActivaParaContrato($idContrato);
        
        if (!$lmat) {
            return null;
        }

        // Obtener el total de días a pagar
        $diasTotales = (int) ($lmat->dias ?? 0);
        
        // Calcular dinámicamente cuántos días ya han sido liquidados
        // (buscando en salarios ya procesados, no modificando la BD)
        $diasYaLiquidados = self::obtenerDiasYaPagados($idContrato, $lmat->id_novedad);
        
        // Calcular días restantes
        $diasRestantes = max(0, $diasTotales - $diasYaLiquidados);

        $tipo = strtoupper(trim((string) ($lmat->tipo_novedad_codigo ?? '')));
        $esMaternidad = $tipo === 'LMAT';

        return [
            'activa' => true,
            'id_novedad' => $lmat->id_novedad,
            'tipo' => $tipo,
            'es_maternidad' => $esMaternidad,
            'dias_totales' => $diasTotales,
            'dias_ya_liquidados' => $diasYaLiquidados,
            'dias_restantes' => $diasRestantes,
            'fecha_inicio' => $lmat->fecha_inicio ? $lmat->fecha_inicio->format('Y-m-d') : null,
            'fecha_fin' => $lmat->fecha_fin ? $lmat->fecha_fin->format('Y-m-d') : null,
            'estado' => $lmat->estado,
        ];
    }

    /**
     * Calcula cuántos días de una LMAT/LPAT ya han sido liquidados en períodos cerrados.
     * 
     * Busca todas las novedades LMAT/LPAT del contrato en salarios ya pagados/liquidados
     * y suma los días registrados.
     * 
     * @param  int $idContrato ID del contrato
     * @param  int $idNovedadActual ID de la novedad actual (para excluir)
     * @return int Días ya liquidados en períodos anteriores
     */
    private static function obtenerDiasYaPagados(int $idContrato, int $idNovedadActual): int
    {
        // Buscar todas las novedades LMAT/LPAT del contrato que ya fueron pagadas
        $diasPagados = DB::table('novedad as n')
            ->join('salario as s', 's.id_salario', '=', 'n.id_salario')
            ->join('periodo_liquidacion as p', 'p.id_periodo', '=', 's.id_periodo')
            ->where('s.id_contrato', $idContrato)
            ->where('n.id_novedad', '!=', $idNovedadActual)
            ->whereIn('n.tipo_novedad_codigo', ['LMAT', 'LPAT'])
            ->where('p.estado', PeriodoLiquidacion::ESTADO_CERRADO)
            ->where('s.estado', \App\Models\Salario::ESTADO_PAGADO)
            ->selectRaw('COALESCE(SUM(n.dias), 0) as dias_totales')
            ->first();

        return (int) ($diasPagados->dias_totales ?? 0);
    }

    /**
     * Verifica si hay una licencia de maternidad/paternidad activa en el periodo.
     * Retorna información sobre si está activa y cuántos días se aplicarían.
     * 
     * @param  int $idContrato ID del contrato
     * @param  int $idPeriodo ID del periodo
     * @param  string $fechaInicioPeriodo Fecha inicio del periodo
     * @param  string $fechaFinPeriodo Fecha fin del periodo
     * @return array ['activa' => bool, 'tipo' => string|null, 'dias_en_periodo' => int]
     */
    public static function obtenerEstadoLmatEnPeriodo(
        int $idContrato,
        int $idPeriodo,
        string $fechaInicioPeriodo,
        string $fechaFinPeriodo
    ): array {
        $lmat = self::obtenerLmatActivaParaContrato($idContrato);

        if (!$lmat) {
            return ['activa' => false, 'tipo' => null, 'dias_en_periodo' => 0];
        }

        // Calcular días de LMAT que caen en este período
        $fechaInicio = \Carbon\Carbon::parse($lmat->fecha_inicio);
        $fechaFin = $lmat->fecha_fin ? \Carbon\Carbon::parse($lmat->fecha_fin) : null;
        
        if (!$fechaFin && $lmat->dias > 0) {
            // Calcular fecha fin desde días
            $fechaFin = $fechaInicio->copy()->addDays($lmat->dias - 1);
        }

        $periodoInicio = \Carbon\Carbon::parse($fechaInicioPeriodo);
        $periodoFin = \Carbon\Carbon::parse($fechaFinPeriodo);

        if (!$fechaFin) {
            return ['activa' => false, 'tipo' => null, 'dias_en_periodo' => 0];
        }

        // Calcular intersección
        if ($fechaFin->lessThan($periodoInicio) || $fechaInicio->greaterThan($periodoFin)) {
            return ['activa' => false, 'tipo' => null, 'dias_en_periodo' => 0];
        }

        $efectivoInicio = $fechaInicio->greaterThan($periodoInicio) ? $fechaInicio : $periodoInicio;
        $efectivoFin = $fechaFin->lessThan($periodoFin) ? $fechaFin : $periodoFin;
        $diasEnPeriodo = max(0, $efectivoInicio->diffInDays($efectivoFin) + 1);

        return [
            'activa' => true,
            'tipo' => strtoupper(trim((string) ($lmat->tipo_novedad_codigo ?? ''))),
            'dias_en_periodo' => $diasEnPeriodo,
            'dias_totales' => (int) ($lmat->dias ?? 0),
            'dias_restantes_rollover' => (int) ($lmat->dias_restantes_rollover ?? 0),
        ];
    }
}
