<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PilaRegistroExport
{
    private Spreadsheet $spreadsheet;
    private $sheet;
    private $registro;
    private $empresa;

    public function __construct($registro, $empresa)
    {
        $this->registro = $registro;
        $this->empresa = $empresa;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    public function export()
    {
        $this->sheet->setTitle('Registro PILA');
        $this->agregarEncabezado();
        $this->agregarDetalles();
        $this->ajustarColumnas();

        return $this->spreadsheet;
    }

    private function agregarEncabezado()
    {
        $fila = 1;

        // Título
        $this->sheet->mergeCells("A{$fila}:C{$fila}");
        $this->sheet->setCellValue("A{$fila}", strtoupper($this->empresa->razon_social ?? 'Empresa'));
        $this->sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(14);
        $this->sheet->getRowDimension($fila)->setRowHeight(20);
        $fila++;

        // Subtítulo
        $this->sheet->mergeCells("A{$fila}:C{$fila}");
        $this->sheet->setCellValue("A{$fila}", 'Registro de Aporte PILA');
        $this->sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(12);
        $this->sheet->getRowDimension($fila)->setRowHeight(16);
        $fila++;

        // NIT
        $this->sheet->setCellValue("A{$fila}", 'NIT:');
        $this->sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $this->sheet->setCellValue("B{$fila}", $this->empresa->nit ?? 'N/A');
        $this->sheet->getRowDimension($fila)->setRowHeight(14);
        $fila++;

        // Espacio
        $fila++;

        return $fila;
    }

    private function agregarDetalles()
    {
        $fila = $this->agregarEncabezado();

        // Obtener valores del registro (puede ser array u objeto)
        $getId = function($key) {
            if (is_array($this->registro)) {
                return $this->registro[$key] ?? null;
            } else {
                return $this->registro->{$key} ?? null;
            }
        };

        // Preparar datos
        $detalles = [
            'Período Inicio' => $this->formatearFecha($getId('fecha_inicio')),
            'Período Fin' => $this->formatearFecha($getId('fecha_fin')),
            'Total Empleados' => (int)($getId('total_empleados') ?? 0),
            'Total Líneas' => (int)($getId('total_lineas') ?? 0),
            'Año Contribución' => $getId('ano_contribucion') ?? 'N/A',
            'Mes Contribución' => $getId('mes_contribucion') ?? 'N/A',
            'Tipo Aporte' => $getId('tipo_aporte') ?? 'N/A',
            'Estado' => $getId('estado') ?? 'Activo',
            'Fecha Generación' => $this->formatearFecha($getId('created_at')),
        ];

        // Agregar filas de detalle
        foreach ($detalles as $etiqueta => $valor) {
            // Etiqueta
            $this->sheet->setCellValue("A{$fila}", $etiqueta);
            $this->sheet->getStyle("A{$fila}")->getFont()->setBold(true);
            $this->sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Valor
            $this->sheet->setCellValue("B{$fila}", $valor);
            $this->sheet->getStyle("B{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $this->sheet->getRowDimension($fila)->setRowHeight(16);
            $fila++;
        }
    }

    private function formatearFecha($fecha)
    {
        if ($fecha === null || $fecha === '') {
            return 'N/A';
        }

        try {
            if ($fecha instanceof \DateTimeInterface) {
                return $fecha->format('d/m/Y');
            }
            return \Carbon\Carbon::parse($fecha)->format('d/m/Y');
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function ajustarColumnas()
    {
        $this->sheet->getColumnDimension('A')->setWidth(20);
        $this->sheet->getColumnDimension('B')->setWidth(30);
        $this->sheet->getColumnDimension('C')->setWidth(20);
    }
}
