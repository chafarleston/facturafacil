<?php

namespace App\Console\Commands;

use App\Services\PrintService;
use Illuminate\Console\Command;

class PrintProcessQueue extends Command
{
    protected $signature = 'print:process-queue';
    protected $description = 'Process pending and failed print jobs';

    public function handle(PrintService $printService)
    {
        $printService->processQueue();
        $this->info('Print queue processed.');
    }
}
