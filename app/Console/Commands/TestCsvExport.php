<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCsvExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-csv-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new \App\Services\Benefits\BenefitPaymentService();
        $batchesData = $service->generarConsignacionAnual(1, 2026);
        file_put_contents('test_batches.json', json_encode($batchesData, JSON_PRETTY_PRINT));
        $this->info('Done!');
    }
}
