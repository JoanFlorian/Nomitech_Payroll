<?php

namespace App\Services\Benefits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class CesantiasExportService
{
    /**
     * Generates a fixed-width TXT file for a collection of employees.
     * Following a standard Asobancaria 2001 style compatible with most funds.
     */
    public function generateTxt(array $employees, int $year): string
    {
        $lines = [];
        foreach ($employees as $emp) {
            $line = '';
            
            // 1. Tipo Doc (2) - pad with zeros
            $line .= str_pad($this->getDocTypeCode($emp['tipo_doc'] ?? 'CC'), 2, '0', STR_PAD_LEFT);
            
            // 2. Documento (15) - pad right with spaces
            $line .= str_pad($emp['document_number'] ?? '', 15, ' ', STR_PAD_RIGHT);
            
            // 3. Primer Apellido (20) - truncate and pad
            $line .= str_pad(mb_substr($this->sanitize($emp['primer_apellido'] ?? ''), 0, 20), 20, ' ', STR_PAD_RIGHT);
            
            // 4. Segundo Apellido (20)
            $line .= str_pad(mb_substr($this->sanitize($emp['segundo_apellido'] ?? ''), 0, 20), 20, ' ', STR_PAD_RIGHT);
            
            // 5. Primer Nombre (20)
            $line .= str_pad(mb_substr($this->sanitize($emp['primer_nombre'] ?? ''), 0, 20), 20, ' ', STR_PAD_RIGHT);
            
            // 6. Otros Nombres (20)
            $line .= str_pad(mb_substr($this->sanitize($emp['otros_nombres'] ?? ''), 0, 20), 20, ' ', STR_PAD_RIGHT);
            
            // 7. Salario Base (12) - pad left with zeros, no decimals
            $line .= str_pad(number_format($emp['salario_base'] ?? 0, 0, '', ''), 12, '0', STR_PAD_LEFT);
            
            // 8. Días Trabajados (3) - pad left with zeros
            $line .= str_pad($emp['dias_trabajados'] ?? 0, 3, '0', STR_PAD_LEFT);
            
            // 9. Valor Cesantías (15) - pad left with zeros
            $line .= str_pad(number_format($emp['amount'] ?? 0, 0, '', ''), 15, '0', STR_PAD_LEFT);
            
            // 10. Año (4)
            $line .= (string) $year;
            
            $lines[] = $line;
        }
        
        return implode("\r\n", $lines);
    }

    /**
     * Generates a styled Excel spreadsheet for the severance liquidation.
     */
    public function generateExcel(array $employees, int $year, string $fundName, string $companyName): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Liquidación Cesantías');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1565C0']
            ]
        ];

        // 1. Company Header
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', strtoupper($companyName));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', "REPORTE DE CONSIGNACIÓN DE CESANTÍAS - AÑO {$year}");
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', "Fondo: {$fundName}");
        $sheet->getStyle('A3')->getFont()->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers
        $headers = [
            'Tipo Doc', 'Documento', 'Primer Apellido', 'Segundo Apellido', 
            'Primer Nombre', 'Otros Nombres', 'F. Ingreso', 'F. Retiro', 
            'Salario Base', 'Días Trab.', 'Periodo', 'Valor Cesantías'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $col++;
        }
        $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);

        // 3. Data
        $row = 6;
        $totalAmount = 0;
        foreach ($employees as $emp) {
            $sheet->setCellValue('A' . $row, $emp['tipo_doc'] ?? '');
            
            // Force Document Number as String to avoid scientific notation (e.g. 1.23E+10)
            $sheet->setCellValueExplicit('B' . $row, $emp['document_number'] ?? '', DataType::TYPE_STRING);
            
            $sheet->setCellValue('C' . $row, $emp['primer_apellido'] ?? '');
            $sheet->setCellValue('D' . $row, $emp['segundo_apellido'] ?? '');
            $sheet->setCellValue('E' . $row, $emp['primer_nombre'] ?? '');
            $sheet->setCellValue('F' . $row, $emp['otros_nombres'] ?? '');
            $sheet->setCellValue('G' . $row, $emp['fecha_ingreso'] ?? '');
            $sheet->setCellValue('H' . $row, $emp['fecha_retiro'] ?? '');
            $sheet->setCellValue('I' . $row, $emp['salario_base'] ?? 0);
            $sheet->setCellValue('J' . $row, $emp['dias_trabajados'] ?? 0);
            $sheet->setCellValue('K' . $row, $emp['periodo_liquidacion'] ?? '');
            $sheet->setCellValue('L' . $row, $emp['amount'] ?? 0);

            // Format monetary columns
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $totalAmount += ($emp['amount'] ?? 0);
            $row++;
        }

        // 4. Totals Row
        $sheet->mergeCells("A{$row}:K{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL A CONSIGNAR');
        $sheet->setCellValue("L{$row}", $totalAmount);
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 5. Auto-size columns (Disabled to avoid dependencies/corruption)
        /*
        foreach (range('A', 'L') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        */

        return $spreadsheet;
    }

    /**
     * Map document types to standard numerical codes.
     */
    private function getDocTypeCode(string $type): string
    {
        return match (strtoupper($type)) {
            'CC' => '1',
            'CE' => '2',
            'TI' => '4',
            'PAS' => '5',
            'RC' => '9',
            'NIT' => '3',
            default => '1'
        };
    }

    /**
     * Sanitize strings for flat file compatibility.
     */
    private function sanitize(string $text): string
    {
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
        ];
        $text = strtr($text, $replacements);
        return strtoupper(preg_replace('/[^A-Za-z0-9 ]+/', '', $text));
    }
}
