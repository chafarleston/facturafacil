<?php

namespace App\Console\Commands;

use App\Services\PrintService;
use Illuminate\Console\Command;

class ProcessPrintQueue extends Command
{
    protected $signature = 'print:process';
    protected $description = 'Process pending print jobs from the queue';

    public function handle(PrintService $printService)
    {
        $this->info('Processing print queue...');
        
        // Call the processQueue method via reflection since it's protected
        $ref = new \ReflectionMethod($printService, 'processQueue');
        $ref->setAccessible(true);
        $ref->invoke($printService);
        
        $this->info('Print queue processed.');
    }
}
