<?php

namespace App\Services;

use Carbon\Carbon;

class PilaFileGeneratorService
{
    private const TASA_SALUD_EMPLEADOR = 0.085;
    private const TASA_PENSION_EMPLEADOR = 0.12;
    private const TASA_CAJA_COMPENSACION = 0.04;

    private float $salarioMinimo;
    private float $fondoSolidaridadRate;

    public function __construct(NominaParameterService $nominaParameterService)
    {
        $params = $nominaParameterService->get();
        $this->salarioMinimo = (float) ($params->smmlv ?? config('pila.salario_minimo', 0));
        $this->fondoSolidaridadRate = (float) ($params->fondo_solidaridad ?? 0.01);
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
            $this->formatInteger($aportes['salud_empleador']),
            $this->formatInteger($aportes['pension_empleador']),
            $this->formatInteger($aportes['fondo_solidaridad']),
            $this->formatInteger($aportes['caja_compensacion']),
            (string) $diasCotizados,
        ]);
    }

    private function calcularAportesRegistroDos(int $ibc): array
    {
        $saludEmpleador = round($ibc * self::TASA_SALUD_EMPLEADOR, 2);
        $pensionEmpleador = round($ibc * self::TASA_PENSION_EMPLEADOR, 2);
        $cajaCompensacion = round($ibc * self::TASA_CAJA_COMPENSACION, 2);

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