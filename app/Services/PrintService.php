<?php

namespace App\Services;

use App\Models\Printer;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $pdf = Pdf::loadView('restaurant.tickets.kitchen', compact('order'))
                ->setPaper([0, 0, 226.77, 1000], 'portrait')
                ->setOption('margin-top', 0)
                ->setOption('margin-right', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('encoding', 'UTF-8');

            $pdfBase64 = base64_encode($pdf->output());
            $this->printServer->printPdf($printer, $pdfBase64);
        }
    }

    public function printPrebill($order): void
    {
        $printer = $this->getPrinter('precuenta');
        if (!$printer) return;

        $company = \App\Models\Company::getMainCompany();
        $pdf = Pdf::loadView('restaurant.tickets.prebill', compact('order', 'company'))
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('encoding', 'UTF-8');

        $pdfBase64 = base64_encode($pdf->output());
        $this->printServer->printPdf($printer, $pdfBase64);
    }

    public function printCancellation($order, $cancelledItem): void
    {
        $dest = $cancelledItem->kds_destination ?? 'cocina';
        $printerKey = $dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1');
        $printer = $this->getPrinter($printerKey);
        if (!$printer) return;

        $html = view('restaurant.tickets.kitchen', compact('order'))->render();
        $pdf = Pdf::loadHTML($html)
            ->setPaper([0, 0, 226.77, 1000], 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0);

        $pdfBase64 = base64_encode($pdf->output());
        $this->printServer->printPdf($printer, $pdfBase64);
    }

    public function printCancelNotification($order, $item): void
    {
        $dest = $item->kds_destination ?? 'cocina';
        $printerKey = $dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1');
        $printer = $this->getPrinter($printerKey);
        if (!$printer) return;

        $text = "================================\n";
        $text .= "        *** ANULACIÓN ***\n";
        $text .= "================================\n";
        $text .= "Pedido: {$order->order_number}\n";
        $text .= "Mesa: {$order->table->name}\n";
        $text .= "Producto: {$item->product_name} x{$item->quantity}\n";
        $text .= "================================";
        $text .= "\n\n\n\n";

        $base64 = base64_encode($text);
        $this->printServer->printPdf($printer, $base64);
    }
}
