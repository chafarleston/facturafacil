<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendToPro51Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        private Invoice $invoice
    ) {}

    public function handle(): void
    {
        try {
            $invoice = $this->invoice->fresh()->load('items', 'customer', 'company');
            if (!$invoice || !$invoice->company) {
                Log::error('pro51 job: invoice or company not found');
                return;
            }
            $controller = app(\App\Http\Controllers\InvoiceController::class);
            $controller->sendToPro51($invoice, $invoice->company);
        } catch (\Exception $e) {
            Log::error('pro51 job failed', [
                'invoice_id' => $this->invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
