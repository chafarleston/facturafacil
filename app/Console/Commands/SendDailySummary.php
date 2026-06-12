<?php

namespace App\Console\Commands;

use App\Services\SummaryService;
use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    protected $signature = 'sunat:send-daily-summary';
    protected $description = 'Agrupa y envía todas las boletas del día en un solo Resumen Diario';

    public function handle(SummaryService $summaryService)
    {
        $this->info('Enviando resumen diario...');

        $result = $summaryService->sendDailySummary();

        if ($result['success']) {
            $this->info('✅ ' . $result['description']);
            return 0;
        } else {
            $this->warn('⚠️ ' . $result['description']);
            return 1;
        }
    }
}
