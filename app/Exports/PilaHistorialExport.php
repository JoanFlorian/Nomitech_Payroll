<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use DateTimeInterface;

class PilaHistorialExport
{
    private Spreadsheet $spreadsheet;
    private $sheet;
    private array $datos;
    private $empresa;

    public function __construct(array $datos, $empresa)
    {
        $this->datos = $datos;
        $this->empresa = $empresa;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    public function export()
    {
        $this->sheet->setTitle('Historial PILA');
        
        $fila = 1;

        // Título principal
        $this->sheet->mergeCells("A{$fila}:E{$fila}");
        $this->sheet->setCellValue("A{$fila}", strtoupper($this->empresa->razon_social ?? 'Empresa'));
        $this->sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(14);
        $this->sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->getRowDimension($fila)->setRowHeight(20);
        $fila++;

        // NIT
        $this->sheet->mergeCells("A{$fila}:E{$fila}");
        $this->sheet->setCellValue("A{$fila}", 'NIT: ' . ($this->empresa->nit ?? 'N/A'));
        $this->sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->getRowDimension($fila)->setRowHeight(16);
        $fila++;

        // Espacio
        $fila++;

        // Encabezados de tabla
        $this->sheet->setCellValue("A{$fila}", '#');
        $this->sheet->setCellValue("B{$fila}", 'Periodo');
        $this->sheet->setCellValue("C{$fila}", 'Empleados');
        $this->sheet->setCellValue("D{$fila}", 'Fecha Generación');
        $this->sheet->setCellValue("E{$fila}", 'Estado');

        for ($col = 'A'; $col <= 'E'; $col++) {
            $this->sheet->getStyle($col . $fila)->getFont()->setBold(true);
            $this->sheet->getStyle($col . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $this->sheet->getRowDimension($fila)->setRowHeight(18);
        $fila++;

        // Datos
        $contador = 1;
        foreach ($this->datos as $item) {
            $periodo = 'N/A';
            if (!empty($item['fecha_inicio']) && !empty($item['fecha_fin'])) {
                try {
                    $inicio = $item['fecha_inicio'];
                    $fin = $item['fecha_fin'];
                    
                    if ($inicio instanceof DateTimeInterface) {
                        $inicio = $inicio->format('Y-m-d');
                    } elseif (is_string($inicio)) {
                        $inicio = \Carbon\Carbon::parse($inicio)->format('Y-m-d');
                    }
                    
                    if ($fin instanceof DateTimeInterface) {
                        $fin = $fin->format('Y-m-d');
                    } elseif (is_string($fin)) {
                        $fin = \Carbon\Carbon::parse($fin)->format('Y-m-d');
                    }
                    
                    $periodo = $inicio . ' a ' . $fin;
                } catch (\Exception $e) {
                    // Ignorar si hay error en parseo de fecha
                }
            }

            // Datos de fila
            $this->sheet->setCellValue('A' . $fila, $contador);
            $this->sheet->setCellValue('B' . $fila, $periodo);
            $this->sheet->setCellValue('C' . $fila, (int) ($item['total_empleados'] ?? 0));
            
            $fechaCreacion = 'N/A';
            if (isset($item['created_at'])) {
                try {
                    $fechaCreacion = \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    // Ignorar si hay error
                }
            }
            $this->sheet->setCellValue('D' . $fila, $fechaCreacion);
            $this->sheet->setCellValue('E' . $fila, 'Disponible');

            $this->sheet->getRowDimension($fila)->setRowHeight(16);
            $contador++;
            $fila++;
        }

        // Ajustar columnas
        $this->sheet->getColumnDimension('A')->setWidth(8);
        $this->sheet->getColumnDimension('B')->setWidth(25);
        $this->sheet->getColumnDimension('C')->setWidth(12);
        $this->sheet->getColumnDimension('D')->setWidth(18);
        $this->sheet->getColumnDimension('E')->setWidth(15);

        return $this->spreadsheet;
    }

    private function agregarEncabezado()
    {
        // Método ya no necesario
    }

    private function agregarTabla()
    {
        // Método ya no necesario
    }

    private function aplicarFormatoFila($fila, $contador)
    {
        // Método ya no necesario
    }

    private function ajustarColumnasYAltos()
    {
        // Método ya no necesario
    }
}
