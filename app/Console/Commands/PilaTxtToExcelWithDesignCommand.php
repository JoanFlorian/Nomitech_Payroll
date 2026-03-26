<?php

namespace App\Console\Commands;

use App\Exports\PilaTxtToExcelWithDesign;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PilaTxtToExcelWithDesignCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pila:txt-to-excel-design {archivo} {--salida=exports/pila_diseño.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte archivo PILA .txt a Excel con diseño profesional (mantiene estructura exacta)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $archivos = glob($this->argument('archivo'));

        if (empty($archivos)) {
            $this->error("❌ Archivo no encontrado: {$this->argument('archivo')}");
            return 1;
        }

        foreach ($archivos as $rutaArchivo) {
            try {
                $this->line("📄 Leyendo archivo: " . basename($rutaArchivo));

                // Crear el export
                $export = new PilaTxtToExcelWithDesign($rutaArchivo);
                $spreadsheet = $export->export();

                $this->line("✅ Archivo parseado correctamente");
                $this->line("✅ Spreadsheet generado (formato con diseño profesional)");

                // Definir ruta de salida
                $salida = $this->option('salida');
                $rutaSalida = storage_path("app/{$salida}");

                // Crear directorio si no existe
                @mkdir(dirname($rutaSalida), 0755, true);

                // Guardar el archivo
                $writer = new Xlsx($spreadsheet);
                $writer->save($rutaSalida);

                $this->line("✅ Excel generado exitosamente");
                $this->info("📁 Ubicación: $rutaSalida");
                $this->info("💾 Descargable en: {$salida}");

                $this->line("");
                $this->line("📋 Características:");
                $this->line("   • Estructura exacta del archivo de texto (.txt)");
                $this->line("   • Mismas columnas y valores sin modificación");
                $this->line("   • Diseño profesional con colores corporativos");
                $this->line("   • Bordes y formato legible");
                $this->line("   • Todas las celdas en formato texto puro");

            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
                return 1;
            }
        }

        return 0;
    }
}
