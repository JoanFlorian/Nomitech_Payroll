<?php

namespace App\Console\Commands;

use App\Exports\PilaTxtToExcelExport;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PilaTxtToExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pila:txt-to-excel
                            {archivo : Ruta del archivo .txt (relativa a storage/app)}
                            {--salida=exports/pila.xlsx : Ruta de salida (relativa a storage/app)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte un archivo PILA .txt a Excel con diseño profesional';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $archivo = $this->argument('archivo');
            $rutaEntrada = storage_path("app/{$archivo}");
            $rutaSalida = storage_path("app/{$this->option('salida')}");

            // Validar que el archivo existe
            if (!file_exists($rutaEntrada)) {
                $this->error("❌ Archivo no encontrado: {$rutaEntrada}");
                return self::FAILURE;
            }

            $this->info("📄 Leyendo archivo: {$archivo}");

            // Crear la exportación
            $export = new PilaTxtToExcelExport($rutaEntrada);
            $this->info("✅ Archivo parseado correctamente");

            // Generar el spreadsheet
            $spreadsheet = $export->export();
            $this->info("✅ Spreadsheet generado");

            // Asegurar que el directorio existe
            $dirSalida = dirname($rutaSalida);
            if (!is_dir($dirSalida)) {
                mkdir($dirSalida, 0755, true);
            }

            // Guardar el archivo
            $writer = new Xlsx($spreadsheet);
            $writer->save($rutaSalida);

            $this->info("✅ Excel generado exitosamente");
            $this->line("📁 Ubicación: {$rutaSalida}");
            $this->line("💾 Descargable en: " . $this->option('salida'));

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            \Log::error('PilaTxtToExcelCommand', [
                'error' => $e->getMessage(),
                'archivo' => $this->argument('archivo'),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
