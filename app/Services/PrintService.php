<?php

namespace App\Services;

use App\Models\Printer;

class PrintService
{
    protected function getPrinter(string $assignedTo): ?Printer
    {
        return Printer::where('assigned_to', $assignedTo)->where('active', true)->first();
    }

    public function getKitchenTickets($order): array
    {
        $groups = ['cocina' => [], 'cocina2' => [], 'bar' => []];
        foreach ($order->items as $item) {
            if ($item->kitchen_status === 'CANCELLED') continue;
            $dest = $item->kds_destination ?? 'cocina';
            if (isset($groups[$dest])) $groups[$dest][] = $item;
        }

        $tickets = [];
        foreach ($groups as $dest => $items) {
            if (empty($items)) continue;
            $printer = $this->getPrinter($dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1'));
            $label = $dest === 'cocina' ? 'Cocina 1' : ($dest === 'cocina2' ? 'Cocina 2' : 'Bar 1');
            $order->setRelation('items', collect($items));
            $data = PlainTextTicket::kitchenTicket($order, 'escpos');
            $tickets[] = [
                'printer' => $printer ? $printer->printer_name : null,
                'ip' => $printer ? $printer->ip_address : null,
                'port' => $printer ? $printer->port : null,
                'type' => $printer ? $printer->type : 'local',
                'label' => $label,
                'data' => base64_encode($data),
            ];
        }
        return $tickets;
    }

    public function getPrebillTicket($order): ?array
    {
        $printer = $this->getPrinter('precuenta');
        if (!$printer) return null;
        $data = PlainTextTicket::prebillTicket($order, 'escpos');
        return [
            'printer' => $printer->printer_name,
            'ip' => $printer->ip_address,
            'port' => $printer->port,
            'type' => $printer->type,
            'data' => base64_encode($data),
        ];
    }

    public function getCancelTickets($order, $items): array
    {
        $groups = ['cocina' => [], 'cocina2' => [], 'bar' => []];
        foreach ($items as $item) {
            $dest = $item->kds_destination ?? 'cocina';
            if (isset($groups[$dest])) $groups[$dest][] = $item;
        }

        $tickets = [];
        foreach ($groups as $dest => $groupItems) {
            if (empty($groupItems)) continue;
            $printerKey = $dest === 'cocina' ? 'cocina-1' : ($dest === 'cocina2' ? 'cocina-2' : 'bar-1');
            $printer = $this->getPrinter($printerKey);
            $order->setRelation('items', collect($groupItems));
            $data = PlainTextTicket::cancelNotificationGrouped($order, 'escpos', $dest);
            $tickets[] = [
                'printer' => $printer ? $printer->printer_name : null,
                'ip' => $printer ? $printer->ip_address : null,
                'port' => $printer ? $printer->port : null,
                'type' => $printer ? $printer->type : 'local',
                'data' => base64_encode($data),
            ];
        }
        return $tickets;
    }

    public function getInvoiceTicket($invoice): ?array
    {
        $printer = $this->getPrinter('caja');
        if (!$printer) return null;
        $data = PlainTextTicket::invoiceTicket($invoice, 'escpos');
        return [
            'printer' => $printer->printer_name,
            'ip' => $printer->ip_address,
            'port' => $printer->port,
            'type' => $printer->type,
            'data' => base64_encode($data),
        ];
    }
}
