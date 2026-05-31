<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Services\Pro51ApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Pro51RetryPending extends Command
{
    protected $signature = 'pro51:retry-pending {invoice_id?}';
    protected $description = 'Reintenta envío de comprobantes pendientes a pro51';

    public function handle(): int
    {
        $invoiceId = $this->argument('invoice_id');

        $query = Invoice::whereHas('company', function ($q) {
            $q->where('facturacion_mode', 'api_externa')
              ->whereNotNull('pro51_activated_at');
        })
        ->whereNull('pro51_external_id')
        ->where('tipo_documento', '!=', 'NV')
        ->whereIn('sunat_estado', ['PENDIENTE', 'RECHAZADO'])
        ->whereColumn('created_at', '>=', \DB::raw('(SELECT pro51_activated_at FROM companies WHERE companies.id = invoices.company_id)'))
        ->orderBy('id');

        if ($invoiceId) {
            $query->where('id', $invoiceId);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->info('No hay comprobantes pendientes');
            return 0;
        }

        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $company = $invoice->company;

            if (!$company || $company->facturacion_mode !== 'api_externa') {
                continue;
            }

            $this->line("Reintentando {$invoice->full_number}...");

            try {
                $service = new Pro51ApiService($company);
                $controller = app(\App\Http\Controllers\InvoiceController::class);
                $controller->sendToPro51($invoice, $company);

                $invoice->refresh();

                if ($invoice->pro51_external_id) {
                    $this->info("  ✓ {$invoice->full_number} enviado");
                    $sent++;
                } else {
                    $this->error("  ✗ {$invoice->full_number}: {$invoice->sunat_description}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ {$invoice->full_number}: {$e->getMessage()}");
                $failed++;
                Log::error('pro51 retry error', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Resultado: {$sent} enviados, {$failed} fallaron");

        return 0;
    }
}
