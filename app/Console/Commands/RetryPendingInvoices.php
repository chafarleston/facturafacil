<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\GreenterService;
use App\Services\SummaryService;
use Illuminate\Console\Command;

class RetryPendingInvoices extends Command
{
    protected $signature = 'sunat:retry-pending {--type=all : Tipo de documento (01=facura, 03=boleta, all=todos)}';
    protected $description = 'Reenvía facturas/boletas pendientes a SUNAT';

    public function handle()
    {
        $type = $this->option('type');

        $query = Invoice::whereIn('sunat_estado', ['PENDIENTE', 'ERROR'])
            ->whereNotNull('tipo_documento');

        if ($type !== 'all') {
            $query->where('tipo_documento', $type);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->info('No hay documentos pendientes');
            return 0;
        }

        $this->info("Procesando {$invoices->count()} documento(s)...");
        $sent = 0;
        $errors = 0;

        foreach ($invoices as $invoice) {
            $this->line("  {$invoice->full_number} ({$invoice->tipo_documento})... ");

            if ($invoice->tipo_documento === '03') {
                $summaryService = new SummaryService();
                $result = $summaryService->sendBoletaToSummary($invoice);
            } elseif ($invoice->tipo_documento === '01') {
                $greenterService = new GreenterService();
                $result = $greenterService->sendInvoice($invoice);
            } else {
                $this->warn("Tipo no soportado: {$invoice->tipo_documento}");
                continue;
            }

            if ($result['success']) {
                $this->info("✅ Enviado");
                $sent++;
            } else {
                $this->warn("⚠️ {$result['description']}");
                $errors++;
            }
        }

        $this->info("Resultado: {$sent} enviados, {$errors} con errores");
        return $errors > 0 ? 1 : 0;
    }
}
