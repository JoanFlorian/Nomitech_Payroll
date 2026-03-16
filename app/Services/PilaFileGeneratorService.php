<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PilaFileGeneratorService
{
    private const TASA_SALUD_EMPLEADOR = 0.085;
    private const TASA_PENSION_EMPLEADOR = 0.12;
    private const TASA_CAJA_COMPENSACION = 0.04;

    private float $salarioMinimo;
    private float $fondoSolidaridadRate;
    private float $epsEmployerRate;
    private float $pensionEmployerRate;
    private float $cajaRate;

    public function __construct(NominaParameterService $nominaParameterService)
    {
        $params = $nominaParameterService->get();
        $this->salarioMinimo = (float) ($params->smmlv ?? config('pila.salario_minimo', 0));
        $this->fondoSolidaridadRate = (float) ($params->fondo_solidaridad ?? 0.01);
        $this->epsEmployerRate = (float) ($params->eps_employer ?? self::TASA_SALUD_EMPLEADOR);
        $this->pensionEmployerRate = (float) ($params->pension_employer ?? self::TASA_PENSION_EMPLEADOR);
        $this->cajaRate = (float) ($params->caja_compensacion ?? self::TASA_CAJA_COMPENSACION);
    }

    public function buildDetallesDesdeNomina(int $empresaId, int $periodoId): array
    {
        $hasIbc = Schema::hasColumn('salario', 'ibc');
        $hasSalarioBase = Schema::hasColumn('salario', 'salario_base');
        $hasDiasTrabajados = Schema::hasColumn('salario', 'dias_trabajados');
        $diasColumn = $hasDiasTrabajados ? 'n.dias_trabajados' : 'n.dias_a_trabajar';
        $ibcExpr = $hasIbc ? 'n.ibc' : 'n.total_devengado';
        $salarioBaseExpr = $hasSalarioBase ? 'n.salario_base' : 'c.salario_base';

        $rows = DB::table('salario as n')
            ->join('contrato as c', 'c.id_contrato', '=', 'n.id_contrato')
            ->join('usuario as u', 'u.doc', '=', 'c.doc')
            ->leftJoin('eps as e', 'e.id_eps', '=', 'c.id_eps')
            ->leftJoin('afp as a', 'a.id_afp', '=', 'c.id_afp')
            ->leftJoin('arl as ar', 'ar.id_arl', '=', 'c.id_arl')
            ->leftJoin('cajas_compensacion as cc', 'cc.id_caja', '=', 'c.id_caja')
            ->where('c.id_empresa', $empresaId)
            ->where('n.id_periodo', $periodoId)
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
                DB::raw("{$salarioBaseExpr} as salario_base"),
                DB::raw("{$ibcExpr} as ibc"),
                DB::raw("{$diasColumn} as dias_trabajados"),
                'n.eps',
                'n.afp',
                'n.arl',
                'n.caja_compensacion',
                'n.aporte_fp',
                'e.codigo_pila as codigo_eps',
                'a.codigo_pila as codigo_afp',
                'ar.codigo_pila as codigo_arl',
                'cc.codigo_pila as codigo_caja',
            ]);

        $detalles = [];

        foreach ($rows as $row) {
            $ibc = round(max(0, (float) ($row->ibc ?? 0)), 2);
            if ($ibc <= 0) {
                continue;
            }

            $diasCotizados = $this->validarDiasCotizados((int) ($row->dias_trabajados ?? 0));
            $aportesPorDefecto = $this->calcularAportesRegistroDos((int) round($ibc));
            $aporteSalud = round(max(0, (float) ($aportesPorDefecto['salud_empleador'] ?? 0)), 2);
            $aportePension = round(max(0, (float) ($aportesPorDefecto['pension_empleador'] ?? 0)), 2);
            $aporteArl = round(max(0, (float) ($row->arl ?? 0)), 2);
            $aporteCaja = round(max(0, (float) ($row->caja_compensacion ?? 0)), 2);
            $aporteFondo = round(max(0, (float) ($row->aporte_fp ?? 0)), 2);

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
                'aporte_salud' => $aporteSalud,
                'aporte_salud_empresa' => $aporteSalud,
                'aporte_pension' => $aportePension,
                'aporte_pension_empresa' => $aportePension,
                'aporte_arl' => $aporteArl,
                'aporte_caja' => $aporteCaja,
                'aporte_fp' => $aporteFondo,
                'dias_cotizados' => $diasCotizados,
                'dias_trabajados' => $diasCotizados,
                'codigo_eps' => (string) ($row->codigo_eps ?? ''),
                'codigo_afp' => (string) ($row->codigo_afp ?? ''),
                'codigo_arl' => (string) ($row->codigo_arl ?? ''),
                'codigo_caja' => (string) ($row->codigo_caja ?? ''),
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

        foreach ($detalles as $detalle) {
            $lineas[] = $this->generateLineaEmpleado($empresaNit, $periodoTexto, $detalle);
        }

        return implode(PHP_EOL, $lineas) . PHP_EOL;
    }

    public function generateLineaEmpleado(string $empresaNit, string $periodo, array $detalle): string
    {
        $ibc = $this->validarIBC((float) ($detalle['ibc'] ?? 0));
        $diasCotizados = $this->validarDiasCotizados((int) ($detalle['dias_cotizados'] ?? 0));
        $aportes = $this->calcularAportesRegistroDos($ibc);
        $aporteSalud = (float) ($detalle['aporte_salud'] ?? $detalle['aporte_salud_empresa'] ?? $aportes['salud_empleador']);
        $aportePension = (float) ($detalle['aporte_pension'] ?? $detalle['aporte_pension_empresa'] ?? $aportes['pension_empleador']);
        $aporteFondo = (float) ($detalle['aporte_fp'] ?? $aportes['fondo_solidaridad']);
        $aporteCaja = (float) ($detalle['aporte_caja'] ?? $aportes['caja_compensacion']);

        $codigoEps = $this->resolverCodigoPilaEntidad($detalle, 'eps', 'codigo_eps');
        $codigoAfp = $this->resolverCodigoPilaEntidad($detalle, 'afp', 'codigo_afp');
        $codigoArl = $this->resolverCodigoPilaEntidad($detalle, 'arl', 'codigo_arl');
        $codigoCaja = $this->resolverCodigoPilaEntidad($detalle, 'cajaCompensacion', 'codigo_caja');

        return implode('|', [
            '02',
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
            $this->formatInteger($aporteFondo),
            $this->formatInteger($aporteCaja),
            (string) $diasCotizados,
        ]);
    }

    private function calcularAportesRegistroDos(int $ibc): array
    {
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
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
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