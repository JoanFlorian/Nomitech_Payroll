<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PilaTxtToExcelWithDesign
{
    private Spreadsheet $spreadsheet;
    private $sheet;
    private string $rutaArchivo;

    // Colores corporativos
    private const COLOR_HEADER_BG = 'FF1a5276';      // Azul oscuro
    private const COLOR_HEADER_TEXT = 'FFFFFFFF';    // Blanco
    private const COLOR_ROW_ALT = 'FFF0f4f7';        // Azul muy claro
    private const COLOR_BORDER = 'FF333333';         // Gris oscuro

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
     * Convierte el archivo .txt a Excel con diseño profesional
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
            
            // Guardar el máximo de columnas
            if (count($campos) > $maxColunas) {
                $maxColunas = count($campos);
            }

            // Colocar cada campo en una columna
            for ($col = 0; $col < count($campos); $col++) {
                $valor = $campos[$col];
                $columnLetter = $this->getColumnLetter($col);
                $celda = $this->sheet->getCell("{$columnLetter}{$fila}");
                
                // Establecer el valor como TEXTO exactamente
                $celda->setDataType(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $celda->setValue($valor);

                // Aplicar formato de texto
                $this->sheet->getStyle("{$columnLetter}{$fila}")
                    ->getNumberFormat()
                    ->setFormatCode('@');

                // Alineación
                $alignment = $this->sheet->getStyle("{$columnLetter}{$fila}")->getAlignment();
                $alignment->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $alignment->setVertical(Alignment::VERTICAL_CENTER);
                $alignment->setWrapText(false);

                // Aplicar estilos de datos con alternancia de colores
                $this->aplicarEstiloDato("{$columnLetter}{$fila}", $fila);
            }

            $fila++;
        }

        // Establecer ancho de columnas
        for ($col = 0; $col < $maxColunas; $col++) {
            $columnLetter = $this->getColumnLetter($col);
            $this->sheet->getColumnDimension($columnLetter)->setWidth(20);
        }

        // Establecer altura de filas
        $this->sheet->getDefaultRowDimension()->setRowHeight(18);

        return $this->spreadsheet;
    }

    /**
     * Aplica estilos a celdas de encabezado
     */
    private function aplicarEstiloEncabezado(string $celda): void
    {
        $style = $this->sheet->getStyle($celda);

        // Fondo azul oscuro
        $style->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB(self::COLOR_HEADER_BG);

        // Texto blanco y negrita
        $style->getFont()
            ->setColor(new Color(self::COLOR_HEADER_TEXT))
            ->setBold(true)
            ->setSize(11);

        // Bordes
        $this->aplicarBordes($celda);
    }

    /**
     * Aplica estilos a celdas de datos
     */
    private function aplicarEstiloDato(string $celda, int $fila): void
    {
        $style = $this->sheet->getStyle($celda);

        // Alternar colores de fila
        if ($fila % 2 === 0) {
            $style->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB(self::COLOR_ROW_ALT);
        }

        // Bordes sutiles
        $this->aplicarBordes($celda);

        // Font normal
        $style->getFont()->setSize(10);
    }

    /**
     * Aplica bordes a una celda
     */
    private function aplicarBordes(string $celda): void
    {
        $style = $this->sheet->getStyle($celda);
        $borders = $style->getBorders();

        $borderStyle = Border::BORDER_THIN;
        $borderColor = self::COLOR_BORDER;

        $borders->getLeft()->setBorderStyle($borderStyle)->setColor(new Color($borderColor));
        $borders->getRight()->setBorderStyle($borderStyle)->setColor(new Color($borderColor));
        $borders->getTop()->setBorderStyle($borderStyle)->setColor(new Color($borderColor));
        $borders->getBottom()->setBorderStyle($borderStyle)->setColor(new Color($borderColor));
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
     * Método estático para crear desde archivo
     */
    public static function fromFile(string $rutaArchivo): Spreadsheet
    {
        $export = new self($rutaArchivo);
        return $export->export();
    }
}
