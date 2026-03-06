<?php

namespace App\Services\Banking;

use App\Models\PeriodoLiquidacion;
use App\Models\Salario;
use App\Models\NominaExportacion;
use App\Models\Empleado;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class BankExportService
{
    /**
     * Generar archivo de exportación bancaria para un periodo cerrado.
     */
    public function generarArchivo(int $id_periodo, string $formato = 'CSV')
    {
        $periodo = PeriodoLiquidacion::findOrFail($id_periodo);

        // 1. Validar periodo cerrado
        if ($periodo->estado !== PeriodoLiquidacion::ESTADO_CERRADO) {
            throw new Exception("Solo se pueden exportar periodos cerrados.");
        }

        // 2. Obtener nóminas liquidadas del periodo
        $nominas = Salario::where('id_periodo', $id_periodo)
            ->where('estado', Salario::ESTADO_LIQUIDADO)
            ->with(['contrato.usuario', 'contrato.cuentaActiva.banco', 'contrato.cuentaActiva.tipoCuenta'])
            ->get();

        if ($nominas->isEmpty()) {
            throw new Exception("No hay nóminas liquidadas para este periodo.");
        }

        // 3. Validar información bancaria
        $erroresBancarios = [];
        $payouts = [];
        $totalPagado = 0;

        foreach ($nominas as $nomina) {
            $contrato = $nomina->contrato;
            $usuario = $contrato->usuario;
            $cuenta = $contrato->cuentaActiva;

            if (!$cuenta || !$cuenta->numero_cuenta || !$cuenta->id_banco || !$cuenta->id_tipo_cuenta) {
                $erroresBancarios[] = "{$usuario->primer_nombre} {$usuario->primer_apellido} ({$usuario->doc})";
                continue;
            }

            $valor = $nomina->salario_neto ?? 0;
            $totalPagado += $valor;

            $payouts[] = [
                'documento' => $usuario->doc,
                'nombre' => "{$usuario->primer_nombre} {$usuario->primer_apellido}",
                'banco' => $cuenta->banco->nombre ?? 'N/A',
                'tipo_cuenta' => $cuenta->tipoCuenta->nombre ?? 'N/A',
                'numero_cuenta' => $cuenta->numero_cuenta,
                'valor' => $valor,
                'referencia' => "NOMINA " . strtoupper($periodo->tipo_frecuencia) . " " . \Carbon\Carbon::parse($periodo->fecha_inicio)->format('M Y'),
            ];
        }

        if (!empty($erroresBancarios)) {
            $msg = "Falta información bancaria para: " . implode(', ', $erroresBancarios);
            throw new Exception($msg);
        }

        // 4. Construir archivo (CSV)
        $fileName = "export_pago_{$periodo->id_periodo}_" . now()->format('YmdHis') . ".csv";
        $filePath = "banking_exports/{$fileName}";

        $csvContent = $this->generateCSV($payouts);
        Storage::disk('public')->put($filePath, $csvContent);

        // 5. Registrar exportación
        $exportacion = NominaExportacion::create([
            'id_periodo' => $id_periodo,
            'id_empresa' => $periodo->id_empresa,
            'formato' => $formato,
            'fecha_generacion' => now(),
            'doc_usuario' => Auth::user()->doc,
            'total_empleados' => count($payouts),
            'total_pagado' => $totalPagado,
            'archivo_path' => $filePath,
        ]);

        return [
            'exportacion' => $exportacion,
            'archivo_url' => Storage::url($filePath),
            'total_empleados' => count($payouts),
            'total_pagado' => $totalPagado
        ];
    }

    private function generateCSV(array $data)
    {
        $handle = fopen('php://temp', 'r+');

        // Header
        fputcsv($handle, ['Documento', 'Nombre', 'Banco', 'TipoCuenta', 'NumeroCuenta', 'Valor', 'Referencia']);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
