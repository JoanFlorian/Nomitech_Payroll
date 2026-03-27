<?php

namespace Tests\Unit;

use App\Exports\PilaTxtToExcelExport;
use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PilaTxtToExcelExportTest extends TestCase
{
    private string $rutaArchivo;

    /**
     * Setup: crear archivo de prueba
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Crear archivo temporal para pruebas
        $contenido = <<<'TXT'
01|830012345|EMPRESA PRUEBA|NI|2026-03|3
02|830012345|2026-03|CC|1070599004|JUAN PEREZ|800100017|1|860006015|2000000|30|280000|320000|43680|80000|0
02|830012345|2026-03|CC|1070599005|MARIA GARCIA|800100017|1|860006015|2500000|30|350000|400000|54600|100000|0
02|830012345|2026-03|CC|1070599006|LUIS RODRIGUEZ|800100017|1|860006015|1800000|25|252000|288000|39312|72000|0
TXT;

        $this->rutaArchivo = tempnam(sys_get_temp_dir(), 'pila_');
        file_put_contents($this->rutaArchivo, $contenido);
    }

    /**
     * Cleanup: borrar archivo temporal
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->rutaArchivo)) {
            unlink($this->rutaArchivo);
        }
    }

    /**
     * Test 1: Crear instancia con archivo válido
     */
    public function test_crear_instancia_con_archivo_valido(): void
    {
        $export = new PilaTxtToExcelExport($this->rutaArchivo);
        $this->assertInstanceOf(PilaTxtToExcelExport::class, $export);
    }

    /**
     * Test 2: Lanzar excepción si archivo no existe
     */
    public function test_excepcion_archivo_no_existe(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Archivo no encontrado');

        new PilaTxtToExcelExport('/ruta/inexistente.txt');
    }

    /**
     * Test 3: Generar Spreadsheet válido
     */
    public function test_generar_spreadsheet_valido(): void
    {
        $export = new PilaTxtToExcelExport($this->rutaArchivo);
        $spreadsheet = $export->export();

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
    }

    /**
     * Test 4: Verificar que el Spreadsheet contiene datos
     */
    public function test_spreadsheet_contiene_datos(): void
    {
        $export = new PilaTxtToExcelExport($this->rutaArchivo);
        $spreadsheet = $export->export();

        $sheet = $spreadsheet->getActiveSheet();

        // Verificar título de la empresa
        $this->assertStringContainsString('EMPRESA PRUEBA', (string)$sheet->getCell('A1')->getValue());

        // Verificar NIT
        $this->assertStringContainsString('830012345', (string)$sheet->getCell('A2')->getValue());
    }

    /**
     * Test 5: Método estático fromFile
     */
    public function test_metodo_estatico_from_file(): void
    {
        $spreadsheet = PilaTxtToExcelExport::fromFile($this->rutaArchivo);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
    }

    /**
     * Test 6: Verificar columnas de encabezado
     */
    public function test_columnas_encabezado(): void
    {
        $export = new PilaTxtToExcelExport($this->rutaArchivo);
        $spreadsheet = $export->export();

        $sheet = $spreadsheet->getActiveSheet();

        // Encontrar la fila de encabezados (debería estar alrededor de fila 6)
        $encontrado = false;
        for ($fila = 1; $fila <= 20; $fila++) {
            $valor = $sheet->getCell("A{$fila}")->getValue();
            if ($valor === 'Tipo Reg.') {
                $encontrado = true;
                break;
            }
        }

        $this->assertTrue($encontrado, 'No se encontró la fila de encabezados');
    }

    /**
     * Test 7: Verificar datos de empleados
     */
    public function test_datos_empleados(): void
    {
        $export = new PilaTxtToExcelExport($this->rutaArchivo);
        $spreadsheet = $export->export();

        $sheet = $spreadsheet->getActiveSheet();

        // Los empleados deberían estar a partir de fila 7 (1 encabezado, 1 título, 1 nit, 1 periodo, 1 empleados, 1 en blanco, 1 encabezados tabla)
        $encontrado = false;
        for ($fila = 1; $fila <= 30; $fila++) {
            $documento = (string)$sheet->getCell("B{$fila}")->getValue();
            if ($documento === '1070599004') {
                $encontrado = true;
                $nombre = $sheet->getCell("C{$fila}")->getValue();
                $this->assertStringContainsString('JUAN', $nombre);
                break;
            }
        }

        $this->assertTrue($encontrado, 'No se encontraron datos de empleados');
    }

    /**
     * Test 8: Verificar que haya totales
     */
    public function test_tiene_totales(): void
    {
        $export = new PilaTxtToExcelExport($this->rutaArchivo);
        $spreadsheet = $export->export();

        $sheet = $spreadsheet->getActiveSheet();

        // Buscar fila de totales
        $encontrado = false;
        for ($fila = 1; $fila <= 50; $fila++) {
            $valor = (string)$sheet->getCell("A{$fila}")->getValue();
            if (strpos($valor, 'TOTAL') !== false) {
                $encontrado = true;
                break;
            }
        }

        $this->assertTrue($encontrado, 'No se encontró fila de totales');
    }

    /**
     * Test 9: Excepción para archivo vacío
     */
    public function test_excepcion_archivo_vacio(): void
    {
        $rutaVacia = tempnam(sys_get_temp_dir(), 'empty_');
        file_put_contents($rutaVacia, '');

        try {
            $this->expectException(\Exception::class);
            new PilaTxtToExcelExport($rutaVacia);
        } finally {
            unlink($rutaVacia);
        }
    }

    /**
     * Test 10: Excepción sin encabezado tipo 01
     */
    public function test_excepcion_sin_encabezado(): void
    {
        $rutaSinEncabezado = tempnam(sys_get_temp_dir(), 'no_header_');
        $contenido = <<<'TXT'
02|830012345|2026-03|CC|1070599004|JUAN PEREZ|800100017|1|860006015|2000000|30|280000|320000|43680|80000|0
TXT;
        file_put_contents($rutaSinEncabezado, $contenido);

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('No se encontró encabezado');
            new PilaTxtToExcelExport($rutaSinEncabezado);
        } finally {
            unlink($rutaSinEncabezado);
        }
    }
}
