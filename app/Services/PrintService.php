<?php

namespace App\Services;

use App\Models\Printer;

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

    public function printKitchenOrder($order): void
    {
        $groups = [
            'cocina' => [],
            'cocina2' => [],
            'bar' => [],
        ];

        foreach ($order->items as $item) {
            if ($item->kitchen_status === 'CANCELLED') continue;
            $dest = $item->kds_destination ?? 'cocina';
            if (isset($groups[$dest])) {
                $groups[$dest][] = $item;
            }
        }

        foreach ($groups as $dest => $items) {
            if (empty($items)) continue;

            $printer = $this->getPrinter($dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1'));
            if (!$printer) continue;

            $order->setRelation('items', collect($items));
            $data = PlainTextTicket::kitchenTicket($order, 'escpos');
            $this->printServer->printText($printer, $data);
        }
    }

    public function printPrebill($order): void
    {
        $printer = $this->getPrinter('precuenta');
        if (!$printer) return;

        $data = PlainTextTicket::prebillTicket($order, 'escpos');
        $this->printServer->printText($printer, $data);
    }

    public function printCancelNotification($order, $item): void
    {
        $dest = $item->kds_destination ?? 'cocina';
        $printerKey = $dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1');
        $printer = $this->getPrinter($printerKey);
        if (!$printer) return;

        $data = PlainTextTicket::cancelNotification($order, $item, 'escpos', $dest);
        $this->printServer->printText($printer, $data);
    }

    public function printInvoice($invoice): void
    {
        $printer = $this->getPrinter('caja');
        if (!$printer) return;

        $data = PlainTextTicket::invoiceTicket($invoice, 'escpos');
        $this->printServer->printText($printer, $data);
    }
}
