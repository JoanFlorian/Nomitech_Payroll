<?php

namespace App\Http\Controllers\Concerns;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HandlesExportResponses
{
    protected function streamSpreadsheetDownload(Spreadsheet $spreadsheet, string $fileName): StreamedResponse
    {
        $this->clearOutputBuffers();

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'public',
        ]);
    }

    protected function downloadPdfResponse(object $pdf, string $fileName)
    {
        $this->clearOutputBuffers();

        return $pdf->download($fileName);
    }

    protected function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }
}
