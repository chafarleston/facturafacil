<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class FixHoraEmision extends Command
{
    protected $signature = 'invoices:fix-hora';
    protected $description = 'Fix hora_emision for existing invoices';

    public function handle()
    {
        $count = Invoice::whereNull('hora_emision')->orWhere('hora_emision', '')->count();
        $this->info("Found {$count} invoices without hora_emision");
        
        Invoice::whereNull('hora_emision')
            ->orWhere('hora_emision', '')
            ->update(['hora_emision' => date('H:i:s')]);
            
        $this->info('Updated all invoices with current time');
        return 0;
    }
}