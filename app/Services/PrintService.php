<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\PrintJob;

class PrintService
{
    protected PrintServerService $printServer;

    public function __construct(PrintServerService $printServer)
    {
        $this->printServer = $printServer;
    }

    protected function getPrinter(string $assignedTo): ?Printer
    {
        return Printer::where('assigned_to', $assignedTo)->where('active', true)->first();
    }

    protected function queuePrint(Printer $printer, string $data, string $jobType, ?string $refType = null, ?int $refId = null): void
    {
        PrintJob::create([
            'printer_name' => $printer->printer_name,
            'printer_ip' => $printer->ip_address,
            'printer_port' => $printer->port,
            'type' => $printer->type,
            'job_type' => $jobType,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'data' => base64_encode($data),
            'status' => 'pending',
        ]);
    }

    public function printKitchenOrder($order, $items = null): void
    {
        $orderItems = $items ?? $order->items;
        $refType = get_class($order);
        $groups = ['cocina' => [], 'cocina2' => [], 'bar' => []];
        foreach ($orderItems as $item) {
            if ($item->kitchen_status === 'CANCELLED') continue;
            $dest = $item->kds_destination ?? 'cocina';
            if (isset($groups[$dest])) $groups[$dest][] = $item;
        }
        foreach ($groups as $dest => $items) {
            if (empty($items)) continue;
            $printer = $this->getPrinter($dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1'));
            if (!$printer) continue;
            $order->setRelation('items', collect($items));
            $data = PlainTextTicket::kitchenTicket($order, 'escpos');
            $this->queuePrint($printer, $data, 'kitchen', $refType, $order->id);
        }
        $this->processQueue();
    }

    public function printPrebill($order, string $printerKey = 'precuenta'): void
    {
        $printer = $this->getPrinter($printerKey);
        if (!$printer) return;
        $data = PlainTextTicket::prebillTicket($order, 'escpos');
        $this->queuePrint($printer, $data, 'prebill', get_class($order), $order->id);
        $this->processQueue();
    }

    public function printCancelNotificationGrouped($order, $items): void
    {
        $groups = ['cocina' => [], 'cocina2' => [], 'bar' => []];
        foreach ($items as $item) {
            $dest = $item->kds_destination ?? 'cocina';
            if (isset($groups[$dest])) $groups[$dest][] = $item;
        }
        foreach ($groups as $dest => $groupItems) {
            if (empty($groupItems)) continue;
            $printerKey = $dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1');
            $printer = $this->getPrinter($printerKey);
            if (!$printer) continue;
            $order->setRelation('items', collect($groupItems));
            $data = PlainTextTicket::cancelNotificationGrouped($order, 'escpos', $dest);
            $this->queuePrint($printer, $data, 'cancel', get_class($order), $order->id);
        }
        $this->processQueue();
    }

    public function printCancelNotification($order, $item): void
    {
        $dest = $item->kds_destination ?? 'cocina';
        $printerKey = $dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1');
        $printer = $this->getPrinter($printerKey);
        if (!$printer) return;
        $data = PlainTextTicket::cancelNotification($order, $item, 'escpos', $dest);
        $this->queuePrint($printer, $data, 'cancel', get_class($order), $order->id);
        $this->processQueue();
    }

    public function printInvoice($invoice): void
    {
        $printer = $this->getPrinter('caja');
        if (!$printer) return;
        $data = PlainTextTicket::invoiceTicket($invoice, 'escpos');
        $this->queuePrint($printer, $data, 'invoice', get_class($invoice), $invoice->id);
        $this->processQueue();
    }

    const MAX_ATTEMPTS = 3;

    public function processQueue(): void
    {
        if (!$this->printServer->isServerRunning()) return;

        $jobs = PrintJob::whereIn('status', ['pending', 'failed'])
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->orderBy('id')
            ->get();

        foreach ($jobs as $job) {
            $job->update(['status' => 'processing', 'attempts' => $job->attempts + 1]);
            try {
                $payload = ['data' => $job->data, 'mode' => 'escpos', 'type' => $job->type];
                if ($job->type === 'network') {
                    $payload['ip'] = $job->printer_ip;
                    $payload['port'] = $job->printer_port;
                } else {
                    $payload['printer'] = $job->printer_name;
                }
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->post(config('print-server.url', 'http://127.0.0.1:9100') . '/print', $payload);
                if ($response->successful()) {
                    $job->update(['status' => 'completed', 'completed_at' => now()]);
                } else {
                    $job->update(['status' => 'failed', 'error_message' => $response->body()]);
                }
            } catch (\Exception $e) {
                $job->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            }
        }
    }
}
