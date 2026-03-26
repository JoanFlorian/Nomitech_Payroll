<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PilaTxtToExcelExport
{
    private Spreadsheet $spreadsheet;
    private ?Worksheet $sheet = null;
    private string $rutaArchivo;
    private array $encabezado = [];
    private array $empleados = [];

    // Colores corporativos (paleta profesional)
    private const COLOR_PRIMARY = 'FF1a5276';      // Azul oscuro
    private const COLOR_SECONDARY = 'FF2874a6';    // Azul medio
    private const COLOR_HEADER_BG = 'FFd4e6f1';    // Azul claro (encabezados)
    private const COLOR_BORDER = '90000000';       // Gris para bordes
    private const COLOR_TEXT_HEADER = 'FFFFFFFF';  // Blanco para texto de header
    private const COLOR_ROW_ALT = 'FFF8f9f9';      // Gris muy suave para filas alternas

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

        // Parsear el archivo
        $this->parseArchivoTxt();
    }

    /**
     * Procesa el archivo txt línea por línea
     */
    private function parseArchivoTxt(): void
    {
        $lineas = file($this->rutaArchivo, FILE_SKIP_EMPTY_LINES);

        if (empty($lineas)) {
            throw new \Exception("El archivo txt está vacío");
        }

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (empty($linea)) {
                continue;
            }

            $tipoRegistro = substr($linea, 0, 2);

            if ($tipoRegistro === '01') {
                // Línea de encabezado
                $this->encabezado = $this->parsearLineaEncabezado($linea);
            } elseif ($tipoRegistro === '02') {
                // Línea de empleado
                $this->empleados[] = $this->parsearLineaEmpleado($linea);
            }
        }

        if (empty($this->encabezado)) {
            throw new \Exception("No se encontró encabezado (tipo 01) en el archivo");
        }
    }

    /**
     * Parsea línea tipo 01 (encabezado)
     * Formato: 01|NIT|NOMBRE|NI|PERIODO|TOTAL_EMPLEADOS
     */
    private function parsearLineaEncabezado(string $linea): array
    {
        $campos = explode('|', $linea);

        return [
            'tipo' => $campos[0] ?? '',
            'nit' => $campos[1] ?? '',
            'nombre_empresa' => $campos[2] ?? '',
            'planilla_type' => $campos[3] ?? '',
            'periodo' => $campos[4] ?? '',
            'total_empleados' => $campos[5] ?? '0',
        ];
    }

    /**
     * Parsea línea tipo 02 (empleado)
     * Formato: 02|NIT|PERIODO|TIPO_DOC|DOCUMENTO|NOMBRE|EPS|AFP|ARL|IBC_SALUD|DIAS_COTIZADOS|
     *          APORTE_SALUD|APORTE_PENSION|VALOR_ARL|APORTE_CAJA|APORTE_FP
     */
    private function parsearLineaEmpleado(string $linea): array
    {
        $campos = explode('|', $linea);

        return [
            'tipo_registro' => $campos[0] ?? '',
            'nit' => $campos[1] ?? '',
            'periodo' => $campos[2] ?? '',
            'tipo_documento' => $campos[3] ?? '',
            'documento' => $campos[4] ?? '',
            'nombre' => $campos[5] ?? '',
            'codigo_eps' => $campos[6] ?? '',
            'codigo_afp' => $campos[7] ?? '',
            'codigo_arl' => $campos[8] ?? '',
            'ibc_salud' => (float) ($campos[9] ?? 0),
            'dias_cotizados' => (int) ($campos[10] ?? 0),
            'aporte_salud' => (float) ($campos[11] ?? 0),
            'aporte_pension' => (float) ($campos[12] ?? 0),
            'valor_arl' => (float) ($campos[13] ?? 0),
            'aporte_caja' => (float) ($campos[14] ?? 0),
            'aporte_fp' => (float) ($campos[15] ?? 0),
        ];
    }

    /**
     * Genera el Excel completo
     */
    public function export(): Spreadsheet
    {
        $this->crearEncabezado();
        $this->crearTablaEmpleados();
        $this->crearResumenTotales();
        $this->ajustarDimensiones();

        return $this->spreadsheet;
    }

    /**
     * Crear encabezado profesional del Excel
     */
    private function crearEncabezado(): void
    {
        $fila = 1;

        // ===== TÍTULO PRINCIPAL =====
        $this->sheet->mergeCells("A{$fila}:H{$fila}");
        $this->sheet->setCellValue("A{$fila}", strtoupper($this->encabezado['nombre_empresa']));
        $this->aplicarEstiloTitulo("A{$fila}");
        $this->sheet->getRowDimension($fila)->setRowHeight(24);
        $fila++;

        // ===== NIT y INFORMACIÓN DE EMPRESA =====
        $this->sheet->mergeCells("A{$fila}:D{$fila}");
        $this->sheet->setCellValue("A{$fila}", "NIT: {$this->encabezado['nit']}");
        $this->aplicarEstiloSubtitulo("A{$fila}");
        $this->sheet->getRowDimension($fila)->setRowHeight(18);
        $fila++;

        // ===== PERIODO =====
        $this->sheet->mergeCells("A{$fila}:D{$fila}");
        $periodo = $this->formatearPeriodo($this->encabezado['periodo']);
        $this->sheet->setCellValue("A{$fila}", "Periodo: {$periodo}");
        $this->aplicarEstiloSubtitulo("A{$fila}");
        $this->sheet->getRowDimension($fila)->setRowHeight(18);
        $fila++;

        // ===== TOTAL EMPLEADOS =====
        $this->sheet->mergeCells("E{$fila}:H{$fila}");
        $this->sheet->setCellValue("E{$fila}", "Total Empleados: " . count($this->empleados));
        $this->aplicarEstiloSubtitulo("E{$fila}");
        $this->sheet->getRowDimension($fila)->setRowHeight(18);
        $fila++;

        // ===== LÍNEA EN BLANCO =====
        $fila++;

        // Guardar la fila del encabezado de tabla
        $this->filaEncabezado = $fila;
    }

    /**
     * Aplicar estilo de título principal
     */
    private function aplicarEstiloTitulo(string $celda): void
    {
        $estilo = $this->sheet->getStyle($celda);
        $estilo->getFont()->setBold(true)->setSize(16)->setColor(new Color(self::COLOR_PRIMARY));
        $estilo->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Aplicar estilo de subtítulo
     */
    private function aplicarEstiloSubtitulo(string $celda): void
    {
        $estilo = $this->sheet->getStyle($celda);
        $estilo->getFont()->setBold(true)->setSize(11)->setColor(new Color(self::COLOR_SECONDARY));
        $estilo->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Formatea el periodo para mostrar (YYYY-MM -> Mes de Año)
     */
    private function formatearPeriodo(string $periodo): string
    {
        if (strlen($periodo) === 7 && strpos($periodo, '-') === 4) {
            [$anio, $mes] = explode('-', $periodo);
            $meses = [
                '01' => 'Enero',
                '02' => 'Febrero',
                '03' => 'Marzo',
                '04' => 'Abril',
                '05' => 'Mayo',
                '06' => 'Junio',
                '07' => 'Julio',
                '08' => 'Agosto',
                '09' => 'Septiembre',
                '10' => 'Octubre',
                '11' => 'Noviembre',
                '12' => 'Diciembre',
            ];

            $nombreMes = $meses[$mes] ?? 'Desconocido';
            return "{$nombreMes} de {$anio}";
        }

        return $periodo;
    }

    /**
     * Crear tabla de empleados con encabezados
     */
    private function crearTablaEmpleados(): void
    {
        $fila = $this->filaEncabezado ?? 6;

        // ===== ENCABEZADOS DE TABLA =====
        $encabezados = [
            'A' => 'Tipo Reg.',
            'B' => 'Documento',
            'C' => 'Nombre',
            'D' => 'EPS',
            'E' => 'AFP',
            'F' => 'ARL',
            'G' => 'IBC',
            'H' => 'Días Cotiz.',
            'I' => 'Aporte Salud',
            'J' => 'Aporte Pensión',
            'K' => 'Valor ARL',
            'L' => 'Aporte Caja',
            'M' => 'Aporte FP',
        ];

        foreach ($encabezados as $columna => $titulo) {
            $this->sheet->setCellValue("{$columna}{$fila}", $titulo);
            $this->aplicarEstiloEncabezado("{$columna}{$fila}");
        }

        $this->sheet->getRowDimension($fila)->setRowHeight(20);
        $fila++;

        // ===== DATOS DE EMPLEADOS =====
        $contador = 0;
        foreach ($this->empleados as $empleado) {
            $this->sheet->setCellValue("A{$fila}", $empleado['tipo_registro']);
            $this->sheet->setCellValue("B{$fila}", $empleado['documento']);
            $this->sheet->setCellValue("C{$fila}", $empleado['nombre']);
            $this->sheet->setCellValue("D{$fila}", $empleado['codigo_eps']);
            $this->sheet->setCellValue("E{$fila}", $empleado['codigo_afp']);
            $this->sheet->setCellValue("F{$fila}", $empleado['codigo_arl']);
            $this->sheet->setCellValue("G{$fila}", $empleado['ibc_salud']);
            $this->sheet->setCellValue("H{$fila}", $empleado['dias_cotizados']);
            $this->sheet->setCellValue("I{$fila}", $empleado['aporte_salud']);
            $this->sheet->setCellValue("J{$fila}", $empleado['aporte_pension']);
            $this->sheet->setCellValue("K{$fila}", $empleado['valor_arl']);
            $this->sheet->setCellValue("L{$fila}", $empleado['aporte_caja']);
            $this->sheet->setCellValue("M{$fila}", $empleado['aporte_fp']);

            // Aplicar estilos de fila
            $this->aplicarEstiloFila($fila, $contador);

            $this->sheet->getRowDimension($fila)->setRowHeight(18);
            $fila++;
            $contador++;
        }

        $this->filaFinal = $fila;
    }

    /**
     * Aplicar estilo de encabezado de tabla
     */
    private function aplicarEstiloEncabezado(string $celda): void
    {
        $estilo = $this->sheet->getStyle($celda);
        
        // Fuente blanca, negrita
        $estilo->getFont()
            ->setBold(true)
            ->setSize(10)
            ->setColor(new Color(self::COLOR_TEXT_HEADER));

        // Fondo azul oscuro
        $estilo->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setStartColor(new Color(self::COLOR_PRIMARY));

        // Alineación centrada
        $estilo->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Bordes
        $this->aplicarBordes($celda);
    }

    /**
     * Aplicar estilos a fila de dato
     */
    private function aplicarEstiloFila(int $fila, int $indice): void
    {
        $rango = "A{$fila}:M{$fila}";
        $estilo = $this->sheet->getStyle($rango);

        // Color de fondo alternado
        if ($indice % 2 === 0) {
            $estilo->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setStartColor(new Color(self::COLOR_ROW_ALT));
        }

        // Alineación y bordes
        $estilo->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $this->aplicarBordes($rango);

        // Formatos específicos por columna
        // Documento: centrado
        $this->sheet->getStyle("B{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Números con formato
        $this->sheet->getStyle("G{$fila}")->getNumberFormat()->setFormatCode('#,##0');
        $this->sheet->getStyle("I{$fila}")->getNumberFormat()->setFormatCode('#,##0');
        $this->sheet->getStyle("J{$fila}")->getNumberFormat()->setFormatCode('#,##0');
        $this->sheet->getStyle("K{$fila}")->getNumberFormat()->setFormatCode('#,##0');
        $this->sheet->getStyle("L{$fila}")->getNumberFormat()->setFormatCode('#,##0');
        $this->sheet->getStyle("M{$fila}")->getNumberFormat()->setFormatCode('#,##0');
    }

    /**
     * Aplicar bordes a una celda o rango
     * Para PhpSpreadsheet 5.x
     */
    private function aplicarBordes(string $rango): void
    {
        $border = new Border();
        
        // Usar propiedades directas para PhpSpreadsheet 5.x
        $borderStyle = [
            'style' => Border::BORDER_THIN,
        ];

        // En PhpSpreadsheet 5.x se usan estas propiedades:
        $border->applyFromArray([
            'top' => $borderStyle,
            'bottom' => $borderStyle,
            'left' => $borderStyle,
            'right' => $borderStyle,
        ]);

        $this->sheet->getStyle($rango)->applyFromArray([
            'borders' => [
                'top' => $borderStyle,
                'bottom' => $borderStyle,
                'left' => $borderStyle,
                'right' => $borderStyle,
            ],
        ]);
    }

    /**
     * Crear fila de resumen con totales
     */
    private function crearResumenTotales(): void
    {
        $fila = ($this->filaFinal ?? 10) + 1;

        // ===== CALCULAR TOTALES =====
        $totalSalud = array_sum(array_column($this->empleados, 'aporte_salud'));
        $totalPension = array_sum(array_column($this->empleados, 'aporte_pension'));
        $totalArl = array_sum(array_column($this->empleados, 'valor_arl'));
        $totalCaja = array_sum(array_column($this->empleados, 'aporte_caja'));
        $totalFp = array_sum(array_column($this->empleados, 'aporte_fp'));

        // ===== FILA DE TOTALES =====
        $this->sheet->mergeCells("A{$fila}:H{$fila}");
        $this->sheet->setCellValue("A{$fila}", "TOTAL APORTES");

        $estilo = $this->sheet->getStyle("A{$fila}");
        $estilo->getFont()->setBold(true)->setSize(11)->setColor(new Color(self::COLOR_TEXT_HEADER));
        $estilo->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->setStartColor(new Color(self::COLOR_SECONDARY));
        $estilo->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Totales en columnas
        $this->sheet->setCellValue("I{$fila}", $totalSalud);
        $this->sheet->setCellValue("J{$fila}", $totalPension);
        $this->sheet->setCellValue("K{$fila}", $totalArl);
        $this->sheet->setCellValue("L{$fila}", $totalCaja);
        $this->sheet->setCellValue("M{$fila}", $totalFp);

        // Aplicar estilos a totales
        for ($col = 'I'; $col <= 'M'; $col++) {
            $celda = "{$col}{$fila}";
            $estilo = $this->sheet->getStyle($celda);
            $estilo->getFont()->setBold(true)->setSize(11)->setColor(new Color(self::COLOR_TEXT_HEADER));
            $estilo->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setStartColor(new Color(self::COLOR_SECONDARY));
            $estilo->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $estilo->getNumberFormat()->setFormatCode('#,##0');
            $this->aplicarBordes($celda);
        }

        $this->sheet->getRowDimension($fila)->setRowHeight(20);
    }

    /**
     * Ajustar dimensiones de columnas
     */
    private function ajustarDimensiones(): void
    {
        $this->sheet->getColumnDimension('A')->setWidth(10);  // Tipo Reg.
        $this->sheet->getColumnDimension('B')->setWidth(15);  // Documento
        $this->sheet->getColumnDimension('C')->setWidth(25);  // Nombre
        $this->sheet->getColumnDimension('D')->setWidth(12);  // EPS
        $this->sheet->getColumnDimension('E')->setWidth(12);  // AFP
        $this->sheet->getColumnDimension('F')->setWidth(12);  // ARL
        $this->sheet->getColumnDimension('G')->setWidth(14);  // IBC
        $this->sheet->getColumnDimension('H')->setWidth(12);  // Días Cotiz.
        $this->sheet->getColumnDimension('I')->setWidth(14);  // Aporte Salud
        $this->sheet->getColumnDimension('J')->setWidth(14);  // Aporte Pensión
        $this->sheet->getColumnDimension('K')->setWidth(12);  // Valor ARL
        $this->sheet->getColumnDimension('L')->setWidth(13);  // Aporte Caja
        $this->sheet->getColumnDimension('M')->setWidth(12);  // Aporte FP

        // Congelar filas de encabezado
        $filaEncabezado = $this->filaEncabezado ?? 6;
        $this->sheet->freezePane("A" . ($filaEncabezado + 1));
    }

    /**
     * Variable para almacenar la fila del encabezado
     */
    private int $filaEncabezado = 6;

    /**
     * Variable para almacenar la fila final
     */
    private int $filaFinal = 10;

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
