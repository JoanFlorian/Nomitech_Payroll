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
        $safeFileName = $this->sanitizeDownloadFileName($fileName, 'export.xlsx');

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $safeFileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'public',
        ]);
    }

    protected function downloadPdfResponse(object $pdf, string $fileName)
    {
        $this->clearOutputBuffers();
        $safeFileName = $this->sanitizeDownloadFileName($fileName, 'documento.pdf');

        return $pdf->download($safeFileName);
    }

    protected function sanitizeDownloadFileName(string $fileName, string $default = 'archivo'): string
    {
        $fileName = trim($fileName);

        if ($fileName === '') {
            return $default;
        }

        $fileName = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $fileName) ?? $default;
        $fileName = preg_replace('/\s+/u', '_', $fileName) ?? $default;
        $fileName = trim($fileName, " ._-");

        if ($fileName === '') {
            return $default;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        if ($name === '') {
            $name = pathinfo($default, PATHINFO_FILENAME) ?: 'archivo';
        }

        $extension = $extension !== '' ? preg_replace('/[^A-Za-z0-9]+/', '', $extension) : '';

        return $extension !== '' ? $name . '.' . $extension : $name;
    }

    protected function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }
}
