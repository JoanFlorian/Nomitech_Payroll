<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PilaTxtToExcelSimple
{
    private Spreadsheet $spreadsheet;
    private $sheet;
    private string $rutaArchivo;

    /**
     * Constructor
     * @param string $rutaArchivo Ruta al archivo .txt
     */
    public function __construct(string $rutaArchivo)
    {
        if (!file_exists($rutaArchivo)) {
            throw new \Exception("Archivo no encontrado: {$rutaArchivo}");
        }

        $this->rutaArchivo = $rutaArchivo;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('PILA');
    }

    /**
     * Convierte el archivo .txt a Excel respetando exactamente el formato
     */
    public function export(): Spreadsheet
    {
        $lineas = file($this->rutaArchivo, FILE_SKIP_EMPTY_LINES);

        if (empty($lineas)) {
            throw new \Exception("El archivo txt está vacío");
        }

        $fila = 1;
        $maxColunas = 0;

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (empty($linea)) {
                continue;
            }

            // Separar por pipe "|"
            $campos = explode('|', $linea);
            
            // Guardar el máximo de columnas para después ajustar
            if (count($campos) > $maxColunas) {
                $maxColunas = count($campos);
            }

            // Colocar cada campo en una columna
            for ($col = 0; $col < count($campos); $col++) {
                $valor = $campos[$col];
                $columnLetter = $this->getColumnLetter($col);
                $celda = $this->sheet->getCell("{$columnLetter}{$fila}");
                
                // PASO 1: Establecer el tipo de dato como STRING ANTES de asignar valor
                $celda->setDataType(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                
                // PASO 2: Asignar el valor exactamente como está (SIN modificar)
                $celda->setValue($valor);
                
                // PASO 3: Aplicar formato de número como TEXTO puro
                $style = $this->sheet->getStyle("{$columnLetter}{$fila}");
                $style->getNumberFormat()->setFormatCode('@');
                
                // PASO 4: Alineación a izquierda
                $style->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(false);
            }

            $fila++;
        }

        // Establecer ancho de columnas GENEROSO
        // Las columnas deben ser lo suficientemente anchas para ver todos los caracteres
        for ($col = 0; $col < $maxColunas; $col++) {
            $columnLetter = $this->getColumnLetter($col);
            
            // Ancho por defecto es 50 caracteres (aproximadamente 30-40 px)
            // Esto debería ser suficiente para nombres, números y todos los datos
            $this->sheet->getColumnDimension($columnLetter)->setWidth(50);
        }

        // Altura de fila automática para que se vea bien
        $this->sheet->getDefaultRowDimension()->setRowHeight(20);

        return $this->spreadsheet;
    }

    /**
     * Convierte número de columna a letra (0 => A, 1 => B, etc.)
     */
    private function getColumnLetter(int $colNum): string
    {
        $letter = '';
        while ($colNum >= 0) {
            $letter = chr(65 + ($colNum % 26)) . $letter;
            $colNum = intdiv($colNum, 26) - 1;
        }
        return $letter;
    }

    /**
     * Obtener el Spreadsheet generado
     */
    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    /**
     * Método estático para crear desde archivo y devolver directamente el Spreadsheet
     */
    public static function fromFile(string $rutaArchivo): Spreadsheet
    {
        $export = new self($rutaArchivo);
        return $export->export();
    }
}

