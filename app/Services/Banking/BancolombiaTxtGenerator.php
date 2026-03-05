<?php

namespace App\Services\Banking;

use App\Models\Salario;
use Exception;

class BancolombiaTxtGenerator implements BankFileGeneratorInterface
{
    /**
     * Genera un archivo TXT simple para Bancolombia.
     *
     * @param int $periodoId
     * @return string
     * @throws Exception
     */
    public function generate(int $periodoId): string
    {
        $salarios = Salario::with(['contrato.usuario', 'contrato.empresa'])
            ->where('id_periodo', $periodoId)
            ->where('estado', Salario::ESTADO_LIQUIDADO)
            ->get();

        if ($salarios->isEmpty()) {
            throw new Exception("No existen liquidaciones en estado 'liquidado' para el periodo #{$periodoId}.");
        }

        // 1. Obtener datos de la empresa (del primer registro)
        $empresa = $salarios->first()->contrato->empresa;
        if (!$empresa) {
            throw new Exception("No se pudo identificar la empresa para el periodo #{$periodoId}.");
        }

        $lineas = [];

        // 2. Registro Tipo 1 - Cabecera
        // 1;NIT_EMPRESA;NOMBRE_EMPRESA;FECHA_PROCESO;NUMERO_LOTE
        $fechaProceso = now()->format('Ymd');
        $lineas[] = implode(';', [
            1,
            $empresa->nit,
            $empresa->razon_social,
            $fechaProceso,
            $periodoId
        ]);

        $totalNeto = 0;

        // 3. Registro Tipo 2 - Detalle
        foreach ($salarios as $salario) {
            $contrato = $salario->contrato;
            $empleado = $contrato->usuario;
            $neto = (float) $salario->salario_neto;

            // Phase 4.2: Strict Decimal Protection
            if (floor($neto) != $neto) {
                throw new Exception("El salario neto del empleado {$empleado->nombre_completo} contiene decimales ({$neto}). Los archivos bancarios solo permiten valores enteros.");
            }

            if ($neto <= 0) {
                throw new Exception("El empleado {$empleado->nombre_completo} tiene un pago neto de {$neto}, lo cual no es permitido en el archivo bancario.");
            }

            // Phase 4.2: Data Source Fix - Pull from Contrato
            $tipoCuenta = $contrato->tipo_cuenta;
            $numeroCuenta = $contrato->numero_cuenta;

            if (empty($tipoCuenta) || empty($numeroCuenta)) {
                throw new Exception("Falta información bancaria (cuenta o tipo) en el CONTRATO del empleado {$empleado->nombre_completo}.");
            }

            // Phase 4.2: Name Sanitization
            $nombre = strtoupper(trim($empleado->nombre_completo));
            $nombre = str_replace(';', '', $nombre);

            // Mapeo tipo cuenta a Bancolombia (A = Ahorros, C = Corriente)
            $tipoBancolombia = (str_starts_with(strtolower($tipoCuenta), 'aho')) ? 'A' : 'C';

            $valorFormateado = (int) $neto;

            $lineas[] = implode(';', [
                2,
                $empleado->id_tipo_doc,
                $empleado->numero_documento,
                $nombre,
                $tipoBancolombia,
                $numeroCuenta,
                $valorFormateado
            ]);

            $totalNeto += $valorFormateado;
        }

        // 4. Registro Tipo 3 - Totales
        $lineas[] = implode(';', [
            3,
            $salarios->count(),
            $totalNeto
        ]);

        // Phase 4.2: Trailing Newline
        return implode("\r\n", $lineas) . "\r\n";
    }
}
