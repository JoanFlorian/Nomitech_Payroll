<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PilaFileGeneratorService
{
    private const TASA_ARL = [
        1 => 0.00522,
        2 => 0.01044,
        3 => 0.02436,
        4 => 0.04350,
        5 => 0.06960,
    ];

    private float $salarioMinimo;
    private float $fondoSolidaridadRate;
    private float $epsEmployeeRate;
    private float $epsEmployerRate;
    private float $pensionEmployeeRate;
    private float $pensionEmployerRate;
    private float $cajaRate;

    public function __construct(NominaParameterService $nominaParameterService)
    {
        $params = $nominaParameterService->get();
        $this->salarioMinimo = max(0, (float) ($params->smmlv ?? 0));
        $this->fondoSolidaridadRate = max(0, (float) ($params->fondo_solidaridad ?? 0));
        $this->epsEmployeeRate = max(0, (float) ($params->eps_employee ?? 0.04));
        $this->epsEmployerRate = max(0, (float) ($params->eps_employer ?? 0.085));
        $this->pensionEmployeeRate = max(0, (float) ($params->pension_employee ?? 0.04));
        $this->pensionEmployerRate = max(0, (float) ($params->pension_employer ?? 0.08));
        $this->cajaRate = max(0, (float) ($params->caja_compensacion ?? 0.04));
    }

    public function buildDetallesDesdeNomina(int $empresaId, int $periodoId): array
    {
        $hasDiasTrabajados = Schema::hasColumn('salario', 'dias_trabajados');
        $diasColumn = $hasDiasTrabajados ? 'n.dias_trabajados' : 'n.dias_a_trabajar';
        $salarioBaseExpr = 'c.salario_base';

        $rows = DB::table('salario as n')
            ->join('contrato as c', 'c.id_contrato', '=', 'n.id_contrato')
            ->join('usuario as u', 'u.doc', '=', 'c.doc')
            ->leftJoin('eps as e', 'e.id_eps', '=', 'c.id_eps')
            ->leftJoin('afp as a', 'a.id_afp', '=', 'c.id_afp')
            ->leftJoin('arl as ar', 'ar.id_arl', '=', 'c.id_arl')
            ->leftJoin('niveles_riesgo as nr', 'nr.id', '=', 'c.nivel_riesgo_id')
            ->leftJoin('cajas_compensacion as cc', 'cc.id_caja', '=', 'c.id_caja')
            ->leftJoin('pila_detalle_empleado as pde', function($join) {
                $join->on('pde.planilla_id', '=', 'n.id_periodo')
                     ->on('pde.doc_empleado', '=', 'u.doc');
            })
            ->leftJoin('novedad as nov', function($join) {
                $join->on('nov.id_salario', '=', 'n.id_salario')
                     ->whereIn('nov.tipo_novedad_codigo', ['LMAT', 'LPAT'])
                     ->where('nov.estado', 'activa');
            })
            ->where('c.id_empresa', $empresaId)
            ->where('n.id_periodo', $periodoId)
            ->whereIn('c.id_tipo_contrato', [1, 2, 3, 4, 5])
            ->orderBy('u.primer_apellido')
            ->orderBy('u.primer_nombre')
            ->get([
                'n.id_salario',
                'u.doc',
                'u.id_tipo_doc',
                'u.primer_nombre',
                'u.otros_nombres',
                'u.primer_apellido',
                'u.segundo_apellido',
                'c.id_tipo_contrato',
                DB::raw("{$salarioBaseExpr} as salario_base"),
                DB::raw("{$diasColumn} as dias_trabajados"),
                'n.aporte_fp',
                'c.nivel_riesgo_id as nivel_riesgo',
                'e.codigo_pila as codigo_eps',
                'a.codigo_pila as codigo_afp',
                'ar.codigo_pila as codigo_arl',
                'cc.codigo_pila as codigo_caja',
                'pde.valor_arl',
                'nov.tipo_novedad_codigo as tipo_novedad',
                'nov.valor_calculado as valor_lmat',
            ]);

        $detalles = [];

        foreach ($rows as $row) {
            $salarioBase    = max(0, (float) ($row->salario_base ?? 0));
            $diasTrabajados = max(0, (int) ($row->dias_trabajados ?? 0));
            $idTipoContrato = (int) ($row->id_tipo_contrato ?? 0);
            
            // Detectar si tiene Licencia de Maternidad/Paternidad activa
            $tieneLmat = in_array($row->tipo_novedad ?? null, ['LMAT', 'LPAT']);
            $valorLmat = (float) ($row->valor_lmat ?? 0);
            
            // IBC: usar valor de LMAT si aplica, sino calcular proporcionalmente
            if ($tieneLmat && $valorLmat > 0) {
                $ibc = (int) round($valorLmat);
            } else {
                // IBC proporcional a los días trabajados, sin mínimo forzado
                $ibc = (int) round(($salarioBase / 30) * $diasTrabajados);
            }

            if ($ibc <= 0) {
                continue;
            }

            $diasCotizados  = $this->validarDiasCotizados($diasTrabajados);
            $nivelRiesgo    = max(1, min(5, (int) ($row->nivel_riesgo ?? 1)));
            $aporteFondo    = (int) round(max(0, (float) ($row->aporte_fp ?? 0)));

            // Cálculo de aportes desde la BD (todos los porcentajes parametrizados)
            // Salud: empleado + empleador (presente en todos los tipos de contrato)
            $salud_total_rate = $this->epsEmployeeRate + $this->epsEmployerRate;
            $aporteSalud = (int) round($ibc * $salud_total_rate);
            
            // Pensión: TODOS pagan (incluyendo aprendices y practicantes) desde 2026
            $pension_total_rate = $this->pensionEmployeeRate + $this->pensionEmployerRate;
            $aportePension = (int) round($ibc * $pension_total_rate);
            
            // ARL y Caja: NO se pagan si está en Licencia de Maternidad/Paternidad
            $aporteCaja = 0;
            $aporteArl = 0;
            
            if (!$tieneLmat) {
                // Caja de compensación: TODOS pagan (incluyendo aprendices y practicantes) desde 2026
                $aporteCaja = (int) round($ibc * $this->cajaRate);
                
                // ARL: presente en todos los tipos de contrato (1, 2, 3, 4, 5)
                // usar valor real de BD, o calcular con tasa si no existe
                $valorArlBd = (float) ($row->valor_arl ?? 0);
                $aporteArl = $valorArlBd > 0 ? (int) round($valorArlBd) : (int) round($ibc * self::TASA_ARL[$nivelRiesgo]);
            }

            $detalles[] = [
                'id_nomina' => (int) ($row->id_salario ?? 0),
                'doc_empleado' => (string) ($row->doc ?? ''),
                'id_tipo_doc' => (int) ($row->id_tipo_doc ?? 0),
                'empleado_nombre' => $this->buildNombreCompleto($row),
                'salario_base' => round(max(0, (float) ($row->salario_base ?? 0)), 2),
                'ibc' => $ibc,
                'ibc_salud' => $ibc,
                'ibc_pension' => $ibc,
                'ibc_arl' => $ibc,
                'ibc_caja' => $ibc,
                'aporte_salud' => $aporteSalud,
                'aporte_salud_empresa' => $aporteSalud,
                'aporte_pension' => $aportePension,
                'aporte_pension_empresa' => $aportePension,
                'aporte_arl' => $aporteArl,
                'valor_arl' => $aporteArl,
                'nivel_riesgo_arl' => $nivelRiesgo,
                'aporte_caja' => $aporteCaja,
                'aporte_fp' => $aporteFondo,
                'dias_cotizados' => $diasCotizados,
                'dias_trabajados' => $diasCotizados,
                'codigo_eps' => (string) ($row->codigo_eps ?? ''),
                'codigo_afp' => (string) ($row->codigo_afp ?? ''),
                'codigo_arl' => (string) ($row->codigo_arl ?? ''),
                'codigo_caja' => (string) ($row->codigo_caja ?? ''),
                'tiene_lmat' => $tieneLmat,
                'tipo_licencia' => $tieneLmat ? $row->tipo_novedad : null,
            ];
        }

        return $detalles;
    }

    public function calcularTotales(array $detalles): array
    {
        return [
            'salud' => (float) array_sum(array_column($detalles, 'aporte_salud')),
            'pension' => (float) array_sum(array_column($detalles, 'aporte_pension')),
            'arl' => (float) array_sum(array_column($detalles, 'aporte_arl')),
            'caja' => (float) array_sum(array_column($detalles, 'aporte_caja')),
        ];
    }

    /**
     * Retorna advertencias por empleado: entidades sin código PILA, documento vacío.
     * No bloquean la generación pero deben mostrarse al usuario.
     */
    public function buildAdvertencias(array $detalles): array
    {
        $advertencias = [];

        foreach ($detalles as $d) {
            $nombre = trim((string) ($d['empleado_nombre'] ?? ''));
            $doc    = trim((string) ($d['doc_empleado']    ?? ''));
            $label  = $nombre !== '' ? $nombre : ($doc !== '' ? $doc : 'empleado desconocido');

            if ($doc === '') {
                $advertencias[] = "{$label}: no tiene número de documento registrado.";
            }

            $entidades = [
                'EPS'                   => (string) ($d['codigo_eps'] ?? ''),
                'AFP (pensión)'         => (string) ($d['codigo_afp'] ?? ''),
                'ARL'                   => (string) ($d['codigo_arl'] ?? ''),
                'Caja de Compensación'  => (string) ($d['codigo_caja'] ?? ''),
            ];

            foreach ($entidades as $nombre_entidad => $codigo) {
                if ($codigo === '' || $codigo === '00') {
                    $advertencias[] = "{$label}: sin código PILA de {$nombre_entidad}.";
                }
            }

            if ((int) ($d['dias_cotizados'] ?? 0) === 0) {
                $advertencias[] = "{$label}: días cotizados es 0.";
            }
        }

        return $advertencias;
    }

    public function guardarArchivoYHistorial(
        int $empresaId,
        int $periodoId,
        object|null $empresa,
        object $periodo,
        array $detalles
    ): array {
        $contenidoTxt = $this->generate($empresa, $periodo, $detalles);
        $nombreArchivo = sprintf(
            'planilla_pila_%d_%d_%s.txt',
            $empresaId,
            $periodoId,
            now()->format('Ymd_His')
        );

        $rutaArchivo = sprintf('pila/%d/%d/%s', $empresaId, $periodoId, $nombreArchivo);
        Storage::disk('local')->put($rutaArchivo, $contenidoTxt);

        if (Schema::hasTable('pila_archivos')) {
            // Limpiar registros anteriores de este período (mantener solo uno)
            $archivosAnteriores = DB::table('pila_archivos')
                ->where('periodo_id', $periodoId)
                ->where('empresa_id', $empresaId)
                ->orderByDesc('id')
                ->get(['id', 'ruta_archivo']);

            // Eliminar archivos físicos y registros anteriores
            foreach ($archivosAnteriores as $archivo) {
                $rutaAnterior = (string) ($archivo->ruta_archivo ?? '');
                if ($rutaAnterior !== '' && Storage::disk('local')->exists($rutaAnterior)) {
                    Storage::disk('local')->delete($rutaAnterior);
                }
                DB::table('pila_archivos')->where('id', $archivo->id)->delete();
            }

            // Insertar solo el nuevo registro
            DB::table('pila_archivos')->insert([
                'periodo_id' => $periodoId,
                'empresa_id' => $empresaId,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'total_empleados' => count($detalles),
                'created_at' => now(),
            ]);
        }

        return [
            'contenido' => $contenidoTxt,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaArchivo,
        ];
    }

    /**
     * Convierte el id de tipo de documento interno al codigo requerido por PILA.
     */
    public function getTipoDocumentoPila(int|string|null $idTipoDoc): string
    {
        return $this->mapTipoDocumentoPila($idTipoDoc);
    }

    public function mapTipoDocumentoPila(int|string|null $idTipoDoc): string
    {
        $default = (string) config('pila.default_document_type', 'CC');
        $map = (array) config('pila.document_type_map', []);

        $key = (int) $idTipoDoc;

        return (string) ($map[$key] ?? $default);
    }

    /**
     * Genera el contenido plano PILA en formato linea por empleado, separado por '|'.
     */
    public function generate(object|null $empresa, object $periodo, array $detalles): string
    {
        $lineas = [];
        $periodoTexto = Carbon::parse($periodo->fecha_inicio)->format('Y-m');
        $empresaNit = preg_replace('/\D+/', '', (string) ($empresa->nit ?? '')) ?: (string) ($empresa->id_empresa ?? '');
        $empresaNombre = $this->formatNombreEmpleado((string) ($empresa->razon_social ?? ''));
        $totalEmpleados = count($detalles);

        // Línea tipo 01 - encabezado del archivo PILA
        $lineas[] = implode('|', ['01', $empresaNit, $empresaNombre, 'NI', $periodoTexto, (string) $totalEmpleados]);

        // Líneas tipo 02 - registros de empleados (sin encabezado de columnas)
        foreach ($detalles as $detalle) {
            $lineas[] = $this->generateLineaEmpleado($empresaNit, $periodoTexto, $detalle);
        }

        return implode(PHP_EOL, $lineas) . PHP_EOL;
    }

    public function generateLineaEmpleado(string $empresaNit, string $periodo, array $detalle): string
    {
        $ibc = $this->validarIBC((float) ($detalle['ibc'] ?? 0));
        $diasCotizados = $this->validarDiasCotizados((int) ($detalle['dias_cotizados'] ?? 0));
        $aporteSalud = (float) ($detalle['aporte_salud'] ?? 0);
        $aportePension = (float) ($detalle['aporte_pension'] ?? 0);
        $valorArl = (float) ($detalle['valor_arl'] ?? 0);
        $aporteFondo = (float) ($detalle['aporte_fp'] ?? 0);
        $aporteCaja = (float) ($detalle['aporte_caja'] ?? 0);

        $codigoEps = $this->resolverCodigoPilaEntidad($detalle, 'eps', 'codigo_eps');
        $codigoAfp = $this->resolverCodigoPilaEntidad($detalle, 'afp', 'codigo_afp');
        $codigoArl = $this->resolverCodigoPilaEntidad($detalle, 'arl', 'codigo_arl');
        $codigoCaja = $this->resolverCodigoPilaEntidad($detalle, 'cajaCompensacion', 'codigo_caja');

        return implode('|', [
            '02', // Tipo de registro empleado PILA
            $empresaNit,
            $periodo,
            $this->mapTipoDocumentoPila($detalle['id_tipo_doc'] ?? null),
            $this->sanitizeField((string) ($detalle['doc_empleado'] ?? '')),
            $this->formatNombreEmpleado((string) ($detalle['empleado_nombre'] ?? '')),
            $codigoEps,
            $codigoAfp,
            $codigoArl,
            $codigoCaja,
            $this->formatInteger($ibc),
            $this->formatInteger($aporteSalud),
            $this->formatInteger($aportePension),
            $this->formatInteger($valorArl), // valor real de la base de datos
            $this->formatInteger($aporteFondo),
            $this->formatInteger($aporteCaja),
            (string) $diasCotizados,
        ]);
    }

    private function calcularAportesRegistroDos(float $ibc): array
    {
        $ibc = max(0, $ibc);
        $saludEmpleador = round($ibc * $this->epsEmployerRate, 2);
        $pensionEmpleador = round($ibc * $this->pensionEmployerRate, 2);
        $cajaCompensacion = round($ibc * $this->cajaRate, 2);

        $umbralSolidaridad = (float) (4 * $this->salarioMinimo);
        $fondoSolidaridad = $ibc > $umbralSolidaridad
            ? round($ibc * $this->fondoSolidaridadRate, 2)
            : 0.0;

        return [
            'salud_empleador' => $saludEmpleador,
            'pension_empleador' => $pensionEmpleador,
            'fondo_solidaridad' => $fondoSolidaridad,
            'caja_compensacion' => $cajaCompensacion,
        ];
    }

    private function buildNombreCompleto(object $row): string
    {
        $partes = [
            (string) ($row->primer_nombre ?? ''),
            (string) ($row->otros_nombres ?? ''),
            (string) ($row->primer_apellido ?? ''),
            (string) ($row->segundo_apellido ?? ''),
        ];


        $partes = array_values(array_filter(array_map('trim', $partes), static fn($v) => $v !== ''));

        return implode(' ', $partes);
    }

    private function resolverCodigoPilaEntidad(array $detalle, string $relacionKey, string $flatKey): string
    {
        $codigo = (string) ($detalle[$flatKey] ?? '');

        if ($codigo === '' && isset($detalle[$relacionKey])) {
            $relacion = $detalle[$relacionKey];

            if (is_array($relacion)) {
                $codigo = (string) ($relacion['codigo_pila'] ?? '');
            } elseif (is_object($relacion)) {
                $codigo = (string) ($relacion->codigo_pila ?? '');
            }
        }

        return $this->sanitizeField($codigo) ?: '00';
    }

    public function formatNombreEmpleado(string $nombre): string
    {
        $texto = $this->sanitizeField($nombre);
        
        // Solo intentar iconv si la extensión está cargada para evitar Error 500
        if (function_exists('iconv')) {
            $prevErrorLevel = error_reporting(0);
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            error_reporting($prevErrorLevel);
            
            if ($transliterated !== false) {
                $texto = $transliterated;
            }
        }
        
        $texto = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $texto) ?? $texto;
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return strtoupper(trim($texto));
    }

    public function validarIBC(float $ibc): int
    {
        $ibcRedondeado = (int) round(max(0, $ibc));
        $minimo = (int) round(max(0, $this->salarioMinimo));

        return max($ibcRedondeado, $minimo);
    }

    public function validarDiasCotizados(int $dias): int
    {
        return max(0, min(30, $dias));
    }

    private function formatInteger(float|int|string $value): string
    {
        return (string) ((int) round((float) $value));
    }

    private function sanitizeField(string $value): string
    {
        return trim(str_replace(["\r", "\n", '|'], [' ', ' ', ' '], $value));
    }
}