<?php

namespace App\Console\Commands;

use App\Services\SummaryService;
use App\Models\SummaryDocument;
use Illuminate\Console\Command;

class CheckSummaryStatus extends Command
{
    protected $signature = 'sunat:check-summaries';
    protected $description = 'Verifica el estado de los resúmenes diarios pendientes';

    public function handle(SummaryService $summaryService)
    {
        $pending = SummaryDocument::where('sunat_estado', 'ENVIADO')
            ->whereNotNull('ticket')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay resúmenes pendientes');
            return 0;
        }

        $this->info('Verificando ' . $pending->count() . ' resúmenes...');

        foreach ($pending as $summary) {
            $this->info('Consultando ticket: ' . $summary->ticket);
            $result = $summaryService->checkTicketStatus($summary->ticket);
            if ($result['success']) {
                $this->info('  → ACEPTADO');
            } else {
                $this->warn('  → ' . $result['description']);
            }
        }

        return 0;
    }
}
