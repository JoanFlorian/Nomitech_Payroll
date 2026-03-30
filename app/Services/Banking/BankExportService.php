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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Banco;
use App\Models\TipoCuenta;

class BankExportService
{
    /**
     * Generar archivo de exportación bancaria para un periodo cerrado.
     */
    public function generarArchivo(int $id_periodo, string $formato = 'CSV', string $tipoExportacion = 'bank')
    {
        $periodo = PeriodoLiquidacion::findOrFail($id_periodo);

        // 1. Validar periodo cerrado
        if ($periodo->estado !== PeriodoLiquidacion::ESTADO_CERRADO) {
            throw new Exception("Solo se pueden exportar periodos cerrados.");
        }

        // 2. Obtener nóminas liquidadas del periodo
        $nominas = Salario::where('id_periodo', $id_periodo)
            ->where('estado', Salario::ESTADO_PAGADO)
            ->with(['contrato.usuario', 'contrato.cuentaActiva.banco', 'contrato.cuentaActiva.tipoCuenta', 'contrato.metodoPago', 'contrato.formaPago'])
            ->get();

        if ($nominas->isEmpty()) {
            throw new Exception("No hay nóminas pagadas para este periodo.");
        }

        // 3. Validar información bancaria
        $erroresBancarios = [];
        $payouts = [];
        $totalPagado = 0;

        foreach ($nominas as $nomina) {
            $contrato = $nomina->contrato;
            $usuario = $contrato->usuario;
            $cuenta = $contrato->cuentaActiva;

            // Verificar si el pago es en efectivo (no requiere cuenta bancaria)
            $formaPago = $contrato->formaPago->nombre ?? '';
            $metodoPago = $contrato->metodoPago->nombre ?? '';
            $esEfectivo = stripos($formaPago, 'efectivo') !== false 
                       || stripos($metodoPago, 'efectivo') !== false
                       || stripos($formaPago, 'contado') !== false
                       || stripos($metodoPago, 'contado') !== false;

            if (!$cuenta || !$cuenta->numero_cuenta || !$cuenta->id_banco || !$cuenta->id_tipo_cuenta) {
                if ($tipoExportacion === 'bank') {
                    if (!$esEfectivo) {
                        $erroresBancarios[] = "{$usuario->primer_nombre} {$usuario->primer_apellido} ({$usuario->doc})";
                    }
                    continue; // En modo 'bank', omitimos a los que no tienen cuenta
                }
            }

            $valor = $nomina->salario_neto ?? 0;
            $totalPagado += $valor;

            $row = [
                'documento' => $usuario->doc,
                'nombre' => "{$usuario->primer_nombre} {$usuario->primer_apellido}",
                'banco' => $cuenta->banco->nombre ?? 'N/A',
                'tipo_cuenta' => $cuenta->tipoCuenta->nombre ?? 'N/A',
                'numero_cuenta' => $cuenta->numero_cuenta ?? 'N/A',
                'valor' => $valor,
                'referencia' => "NOMINA " . strtoupper($periodo->tipo_frecuencia) . " " . \Carbon\Carbon::parse($periodo->fecha_inicio)->format('M Y'),
            ];

            if ($tipoExportacion === 'general') {
                $nombreMetodo = $metodoPago ?: ($formaPago ?: 'N/A');
                $row['metodo_pago'] = $nombreMetodo;
            }

            $payouts[] = $row;
        }

        if ($tipoExportacion === 'bank' && !empty($erroresBancarios)) {
            $msg = "Falta información bancaria para: " . implode(', ', $erroresBancarios);
            throw new Exception($msg);
        }

        // 4. Construir archivo (CSV)
        $fileName = "export_pago_{$periodo->id_periodo}_" . now()->format('YmdHis') . ".csv";
        $filePath = "banking_exports/{$fileName}";

        $headers = ['Documento', 'Nombre', 'Banco', 'TipoCuenta', 'NumeroCuenta', 'Valor', 'Referencia'];
        if ($tipoExportacion === 'general') {
            $headers[] = 'MetodoPago';
            // General siempre devuelve Excel estilo PAB por solicitud del usuario
            return $this->generarResumenGeneralExcel($id_periodo, $payouts, $totalPagado);
        }

        $csvContent = $this->generateCSV($payouts, $headers);
        // Si no es general, seguimos con el CSV tradicional para 'bank'
        $csvContent = $this->generateCSV($payouts, $headers);
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

    /**
     * Generar un resumen general en Excel con TODOS los empleados.
     */
    private function generarResumenGeneralExcel(int $id_periodo, array $payouts, float $totalPagado): array
    {
        $periodo = PeriodoLiquidacion::findOrFail($id_periodo);
        $empresa = \App\Models\Empresa::findOrFail($periodo->id_empresa);

        $fileName = "resumen_nomina_{$id_periodo}_" . now()->format('YmdHis') . ".xlsx";
        $filePath = "banking_exports/{$fileName}";

        $data = [
            'header' => [
                'fecha_envio' => now()->format('d/m/Y'),
                'nit'         => $empresa->nit,
                'empresa'     => $empresa->razon_social,
                'cuenta'      => 'N/A',
                'tipo_cuenta' => 'MULTIPLE',
                'total'       => $totalPagado,
                'registros'   => count($payouts),
                'secuencia'   => 'N/A',
                'titulo'      => 'RESUMEN GENERAL DE NOMINA'
            ],
            'detalles' => $payouts
        ];

        $this->generateExcelBase($data, $filePath, true);

        $exportacion = NominaExportacion::create([
            'id_periodo' => $id_periodo,
            'id_empresa' => $periodo->id_empresa,
            'formato' => 'XLSX',
            'fecha_generacion' => now(),
            'doc_usuario' => Auth::user()->doc,
            'total_empleados' => count($payouts),
            'total_pagado' => $totalPagado,
            'archivo_path' => $filePath, // El Excel es el archivo principal aquí
            'archivo_excel_path' => $filePath,
        ]);

        return [
            'exportacion' => $exportacion,
            'archivo_url' => Storage::url($filePath),
            'total_empleados' => count($payouts),
            'total_pagado' => $totalPagado
        ];
    }

    /**
     * Generar archivo PAB (Pagos Automatizados Bancolombia) para un periodo cerrado.
     */
    public function generarArchivoPab(
        int $id_periodo,
        string $cuentaDebito,
        string $tipoCuentaDebito,
        string $secuencia = 'A1'
    ): array {
        $periodo = PeriodoLiquidacion::findOrFail($id_periodo);

        // 1. Validar periodo cerrado
        if ($periodo->estado !== PeriodoLiquidacion::ESTADO_CERRADO) {
            throw new Exception("Solo se pueden exportar periodos cerrados.");
        }

        $empresa = \App\Models\Empresa::findOrFail($periodo->id_empresa);

        // 2. Obtener nóminas liquidadas con relaciones bancarias
        $nominas = Salario::where('id_periodo', $id_periodo)
            ->where('estado', Salario::ESTADO_PAGADO)
            ->with([
                'contrato.usuario',
                'contrato.cuentaActiva.banco',
                'contrato.cuentaActiva.tipoCuenta',
                'contrato.metodoPago',
            ])
            ->get();

        if ($nominas->isEmpty()) {
            throw new Exception("No hay nóminas pagadas para este periodo.");
        }

        // 3. Filtrar y validar empleados
        $errores = [];
        $empleadosData = [];
        $totalPagado = 0;

        foreach ($nominas as $nomina) {
            $contrato = $nomina->contrato;
            $usuario = $contrato->usuario;
            $cuenta = $contrato->cuentaActiva;
            $metodoPago = $contrato->metodoPago->nombre ?? '';

            // Excluir empleados con pago en efectivo
            if (stripos($metodoPago, 'efectivo') !== false) {
                continue;
            }

            // Validar cuenta bancaria
            if (!$cuenta || !$cuenta->numero_cuenta) {
                $errores[] = "{$usuario->primer_nombre} {$usuario->primer_apellido} ({$usuario->doc}): sin cuenta bancaria";
                continue;
            }

            // Validar banco con código ACH
            $banco = $cuenta->banco;
            if (!$banco || empty($banco->codigo_ach)) {
                $errores[] = "{$usuario->primer_nombre} {$usuario->primer_apellido} ({$usuario->doc}): banco sin código ACH";
                continue;
            }

            // Validar tipo de documento
            $idTipoDoc = $usuario->id_tipo_doc ?? 1;

            // Determinar tipo de transacción para el archivo plano y etiqueta para el Excel
            $tipoCuentaNom = $cuenta->tipoCuenta->nombre ?? 'Ahorros';
            if (stripos($metodoPago, 'billetera') !== false || stripos($tipoCuentaNom, 'depósito') !== false || stripos($tipoCuentaNom, 'digital') !== false) {
                $tipoTransaccionLabel = 'ABONO DEPOSITO ELECTRONICO';
            } elseif (stripos($tipoCuentaNom, 'corriente') !== false) {
                $tipoTransaccionLabel = 'ABONO CUENTA CORRIENTE';
            } else {
                $tipoTransaccionLabel = 'ABONO CUENTA DE AHORROS';
            }

            $valor = (float) ($nomina->salario_neto ?? 0);
            $totalPagado += $valor;

            $empleadosData[] = [
                'tipo_doc_id'     => $idTipoDoc,
                'documento'       => $usuario->doc,
                'nombre'          => trim(
                    ($usuario->primer_nombre ?? '') . ' ' .
                    ($usuario->otros_nombres ?? '') . ' ' .
                    ($usuario->primer_apellido ?? '') . ' ' .
                    ($usuario->segundo_apellido ?? '')
                ),
                'tipo_transaccion' => $tipoTransaccionLabel,
                'codigo_ach'       => $banco->codigo_ach,
                'numero_cuenta'    => $cuenta->numero_cuenta,
                'email'            => $usuario->correo ?? '',
                'valor'            => $valor,
            ];
        }

        if (!empty($errores)) {
            throw new Exception("Errores de validación:\n• " . implode("\n• ", $errores));
        }

        if (empty($empleadosData)) {
            throw new Exception("No hay empleados válidos para generar el archivo PAB (todos pagan en efectivo o no tienen datos bancarios).");
        }

        // 4. Generar archivo PAB
        $generator = new PabFileGenerator($empresa, $cuentaDebito, $tipoCuentaDebito, $secuencia);
        $contenido = $generator->generate($empleadosData);

        // 5. Guardar archivo TXT
        $fileNameTxt = "pab_nomina_{$periodo->id_periodo}_" . now()->format('YmdHis') . ".txt";
        $filePathTxt = "banking_exports/{$fileNameTxt}";
        Storage::disk('public')->put($filePathTxt, $contenido);

        $fileNameXls = "pab_nomina_{$periodo->id_periodo}_" . now()->format('YmdHis') . ".xlsx";
        $filePathXls = "banking_exports/{$fileNameXls}";

        $data = [
            'header' => [
                'fecha_envio' => now()->format('d/m/Y'),
                'nit'         => $empresa->nit,
                'empresa'     => $empresa->razon_social,
                'cuenta'      => $cuentaDebito,
                'tipo_cuenta' => strtoupper($tipoCuentaDebito) === 'S' ? 'Ahorros' : 'Corriente',
                'total'       => $totalPagado,
                'registros'   => count($empleadosData),
                'secuencia'   => $secuencia,
                'titulo'      => 'RESUMEN DE DISPERSION BANCARIA (PAB)'
            ],
            'detalles' => $empleadosData
        ];

        $this->generateExcelBase($data, $filePathXls);

        // 6. Registrar exportación
        $exportacion = NominaExportacion::create([
            'id_periodo'      => $id_periodo,
            'id_empresa'      => $periodo->id_empresa,
            'formato'         => 'PAB',
            'fecha_generacion' => now(),
            'doc_usuario'     => Auth::user()->doc ?? 'SYSTEM',
            'total_empleados' => count($empleadosData),
            'total_pagado'    => $totalPagado,
            'archivo_path'    => $filePathTxt,
            'archivo_excel_path' => $filePathXls,
        ]);

        return [
            'exportacion'      => $exportacion,
            'archivo_url'      => Storage::url($filePathTxt),
            'archivo_excel_url' => Storage::url($filePathXls),
            'total_empleados'  => count($empleadosData),
            'total_pagado'     => $totalPagado,
        ];
    }

    /**
     * Base de generación de Excel con estilo premium.
     */
    private function generateExcelBase(array $data, string $filePath, bool $isGeneral = false): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $h = $data['header'];

        // 1. Cabecera visual
        $sheet->setCellValue('A1', $h['titulo'] ?? 'RESUMEN DE NOMINA');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $headerFields = [
            'FECHA DE ENVIO'        => $h['fecha_envio'],
            'NIT'                   => $h['nit'],
            'NOMBRE DE LA EMPRESA'  => $h['empresa'],
            'CUENTA DE DEBITO'      => $h['cuenta'],
            'TIPO DE CUENTA'        => $h['tipo_cuenta'],
            'VALOR TOTAL'           => $h['total'],
            'NUMERO DE REGISTROS'   => $h['registros'],
            'SECUENCIA'             => $h['secuencia'],
        ];

        $row = 3;
        foreach ($headerFields as $label => $value) {
            $sheet->setCellValue('A' . $row, $label);
            if ($label === 'NIT' || $label === 'CUENTA DE DEBITO') {
                $sheet->setCellValueExplicit('B' . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue('B' . $row, $value);
            }
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            
            if ($label === 'VALOR TOTAL') {
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            }
            $row++;
        }

        // 2. Detalle de empleados
        $row += 2;
        $startDetailRow = $row;
        
        $columns = [
            'TIPO DE DOCUMENTO',
            'DOCUMENTO',
            'NOMBRE',
            'BANCO',
            'NUMERO DE CUENTA',
            'TIPO TRANSACCION',
            'VALOR',
            'EMAIL'
        ];

        if ($isGeneral) {
            $columns[] = 'METODO DE PAGO';
        }

        $col = 'A';
        foreach ($columns as $title) {
            $sheet->setCellValue($col . $row, $title);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E0E0E0');
            $col++;
        }

        $row++;
        foreach ($data['detalles'] as $emp) {
            // Mapear banco nombre
            $bancoNombre = isset($emp['codigo_ach']) 
                ? (Banco::where('codigo_ach', $emp['codigo_ach'])->value('nombre') ?? $emp['codigo_ach'])
                : ($emp['banco'] ?? 'N/A');

            $tipoDoc = isset($emp['tipo_doc_id']) 
                ? PabFileGenerator::mapTipoDocumento($emp['tipo_doc_id'])
                : 'CC';

            $sheet->setCellValue('A' . $row, $tipoDoc);
            $sheet->setCellValueExplicit('B' . $row, $emp['documento'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $emp['nombre']);
            $sheet->setCellValue('D' . $row, $bancoNombre);
            $sheet->setCellValueExplicit('E' . $row, $emp['numero_cuenta'] ?? 'N/A', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $row, $emp['tipo_transaccion'] ?? 'N/A');
            $sheet->setCellValue('G' . $row, $emp['valor']);
            $sheet->setCellValue('H' . $row, $emp['email'] ?? 'N/A');

            if ($isGeneral) {
                $sheet->setCellValue('I' . $row, $emp['metodo_pago'] ?? 'N/A');
            }

            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            $row++;
        }

        $maxCol = $isGeneral ? 'I' : 'H';
        foreach (range('A', $maxCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fullPath = Storage::disk('public')->path($filePath);
        
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($fullPath);
    }

    private function generateCSV(array $data, array $headers)
    {
        $handle = fopen('php://temp', 'r+');

        // Header
        fputcsv($handle, $headers);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
