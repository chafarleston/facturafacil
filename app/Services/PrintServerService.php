<?php

namespace App\Services;

use App\Models\Printer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintServerService
{
    protected string $serverUrl;

    public function __construct()
    {
        $this->serverUrl = config('print-server.url', 'http://127.0.0.1:9100');
    }

    public function isServerRunning(): bool
    {
        try {
            $response = Http::timeout(3)->get("{$this->serverUrl}/status");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAvailablePrinters(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->serverUrl}/printers");
            if ($response->successful()) {
                return $response->json('printers', []);
            }
        } catch (\Exception $e) {
            Log::error('PrintServer: Failed to get printers: ' . $e->getMessage());
        }
        return [];
    }

    public function printPdf(Printer $printer, string $pdfBase64): bool
    {
        return $this->sendPrint($printer, $pdfBase64, 'pdf');
    }

    public function printText(Printer $printer, string $text): bool
    {
        return $this->sendPrint($printer, $text, 'escpos');
    }

    protected function sendPrint(Printer $printer, string $content, string $mode): bool
    {
        try {
            $payload = [
                'data' => $content,
                'type' => $printer->type,
                'mode' => $mode,
            ];

            if ($printer->type === 'network') {
                $payload['ip'] = $printer->ip_address;
                $payload['port'] = $printer->port;
            } else {
                $payload['printer'] = $printer->printer_name;
            }

            $response = Http::timeout(30)->post("{$this->serverUrl}/print", $payload);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("PrintServer: Print failed for {$printer->name}: " . $e->getMessage());
            return false;
        }
    }

    public function getPrinterFor(string $assignedTo): ?Printer
    {
        return Printer::where('assigned_to', $assignedTo)->where('active', true)->first();
    }
}
