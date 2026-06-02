<?php

namespace App\Services;

class PlainTextTicket
{
    protected int $width = 42;
    protected array $lines = [];

    // ESC/POS commands
    const ESC = "\x1B";
    const GS = "\x1D";
    const LF = "\x0A";
    const INIT = "\x1B\x40";
    const ALIGN_LEFT = "\x1B\x61\x00";
    const ALIGN_CENTER = "\x1B\x61\x01";
    const ALIGN_RIGHT = "\x1B\x61\x02";
    const BOLD_ON = "\x1B\x45\x01";
    const BOLD_OFF = "\x1B\x45\x00";
    const DOUBLE_ON = "\x1B\x21\x30";
    const DOUBLE_OFF = "\x1B\x21\x00";
    const CUT = "\x1D\x56\x00";
    const FEED = "\x1B\x64\x05";
    const QR_MODEL = "\x1D\x28\x6B\x04\x00\x31\x41\x32\x00";
    const QR_SIZE = "\x1D\x28\x6B\x03\x00\x31\x43\x06";
    const QR_EC = "\x1D\x28\x6B\x03\x00\x31\x45\x30";
    const QR_PRINT = "\x1D\x28\x6B\x03\x00\x31\x51\x30";

    public function center(string $text, string $char = ' '): void
    {
        $text = $this->clean($text);
        $pad = max(0, $this->width - strlen($text));
        $left = intdiv($pad, 2);
        $right = $pad - $left;
        $this->lines[] = str_repeat($char, $left) . $text . str_repeat($char, $right);
    }

    public function left(string $text): void
    {
        $this->lines[] = $this->clean(mb_substr($text, 0, $this->width));
    }

    public function right(string $text): void
    {
        $text = $this->clean($text);
        $pad = max(0, $this->width - strlen($text));
        $this->lines[] = str_repeat(' ', $pad) . $text;
    }

    public function twoColumns(string $left, string $right, string $glue = ' '): void
    {
        $left = $this->clean($left);
        $right = $this->clean($right);
        $available = $this->width - strlen($right);
        $left = mb_substr($left, 0, $available - 1);
        $dots = $this->width - strlen($left) - strlen($right);
        $this->lines[] = $left . str_repeat($glue, max(0, $dots)) . $right;
    }

    public function itemLine(string $qty, string $name, string $total): void
    {
        $qty = $this->clean($qty);
        $total = $this->clean($total);
        $name = $this->clean(mb_substr($name, 0, $this->width - strlen($qty) - strlen($total) - 2));
        $dots = $this->width - strlen($qty) - strlen($name) - strlen($total);
        $this->lines[] = $qty . ' ' . $name . str_repeat('.', max(0, $dots)) . $total;
    }

    public function separator(string $char = '-'): void
    {
        $this->lines[] = str_repeat($char, $this->width);
    }

    public function blank(): void
    {
        $this->lines[] = '';
    }

    public function text(string $text): void
    {
        $this->lines[] = $this->clean($text);
    }

    public function getText(): string
    {
        return implode("\n", $this->lines) . "\n";
    }

    public function getEscPos(): string
    {
        $out = self::INIT;
        $out .= self::ALIGN_CENTER;

        $first = true;
        foreach ($this->lines as $line) {
            $trimmed = trim($line);

            if (str_contains($trimmed, self::BOLD_ON) || str_contains($trimmed, self::DOUBLE_ON)) {
                $out .= self::ALIGN_CENTER . $trimmed . self::LF;
                $first = false;
                continue;
            }

            if ($first) {
                $out .= self::BOLD_ON . strtoupper($trimmed) . self::BOLD_OFF . self::LF;
                $first = false;
                continue;
            }
            if (str_starts_with($trimmed, '*** ') || str_starts_with($trimmed, '** ')) {
                $out .= self::ALIGN_CENTER . self::BOLD_ON . $trimmed . self::BOLD_OFF . self::LF;
            } elseif (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '==')) {
                $out .= self::ALIGN_LEFT . $trimmed . self::LF;
            } elseif ($trimmed === '') {
                $out .= self::LF;
            } else {
                $out .= self::ALIGN_LEFT . $trimmed . self::LF;
            }
        }
        $out .= self::LF . self::FEED;
        if ($this->qrData) {
            $out .= $this->buildQR();
        }
        $out .= self::CUT;
        return $out;
    }

    protected string $qrData = '';

    public function setQR(string $data): void
    {
        $this->qrData = $data;
    }

    protected function buildQR(): string
    {
        $data = $this->qrData;
        $len = strlen($data) + 3;
        $pL = $len & 0xFF;
        $pH = ($len >> 8) & 0xFF;
        $out = self::QR_MODEL;
        $out .= self::QR_SIZE;
        $out .= self::QR_EC;
        $out .= "\x1D\x28\x6B{$pL}{$pH}\x31\x50\x30";
        $out .= $data;
        $out .= self::ALIGN_CENTER;
        $out .= self::QR_PRINT;
        $out .= self::LF;
        return $out;
    }

    protected function clean(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        return strtr($text, [
            'Á' => "\x41", 'É' => "\x90", 'Í' => "\xB6", 'Ó' => "\xE0", 'Ú' => "\xE9",
            'á' => "\xA0", 'é' => "\x82", 'í' => "\xA1", 'ó' => "\xA2", 'ú' => "\xA3",
            'Ñ' => "\xA4", 'ñ' => "\xA5", 'Ü' => "\x9A", 'ü' => "\x9A",
            '¿' => "\xA8", '¡' => "\xAD", 'º' => "\xA7",
        ]);
    }

    public static function kitchenTicket($order, string $format = 'text'): string
    {
        $t = new self();
        $dest = $order->items->first()->kds_destination ?? 'cocina';
        $t->buildKitchenHeader($order, $dest);
        foreach ($order->items as $item) {
            if ($item->kitchen_status === 'CANCELLED') continue;
            $t->itemLine("{$item->quantity}x", $item->product_name, '');
            if ($item->notes) $t->text('  Obs: ' . $item->notes);
        }
        $t->separator();
        $footer = match($dest) { 'cocina2' => 'COCINA 2', 'bar' => 'BAR', default => 'COCINA 1' };
        $t->center("**** $footer ****");
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    public static function prebillTicket($order, string $format = 'text'): string
    {
        $t = new self();
        $t->buildPrebillHeader($order);
        $activeItems = $order->items->where('kitchen_status', '!=', 'CANCELLED');
        foreach ($activeItems as $item) {
            $t->itemLine("{$item->quantity}x", $item->product_name, 'S/ ' . number_format($item->total, 2));
            if ($item->notes) $t->text('  ' . $item->notes);
        }
        $t->separator();
        $total = $activeItems->sum('total');
        $company = \App\Models\Company::find($order->company_id);
        $igvPct = $company ? $company->getActiveIgvPercent() : 18;
        $igvRate = $igvPct / 100;
        $subtotal = $total / (1 + $igvRate);
        $igv = $total - $subtotal;
        $t->twoColumns('Subtotal:', 'S/ ' . number_format($subtotal, 2));
        $t->twoColumns('IGV (' . $igvPct . '%):', 'S/ ' . number_format($igv, 2));
        $t->separator('=');
        $t->twoColumns('TOTAL:', 'S/ ' . number_format($total, 2));
        $t->separator();
        $t->center(now()->format('d/m/Y H:i:s'));
        $t->center('**** PRECUENTA ****');
        $t->center('Gracias por su visita');
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    public static function invoiceTicket($invoice, string $format = 'text'): string
    {
        $t = new self();
        $company = \App\Models\Company::getMainCompany();
        $t->center($company->nombre_comercial ?? $company->razon_social ?? 'Restaurante');
        if ($company->ruc) $t->center('RUC: ' . $company->ruc);
        if ($company->direccion) $t->center($company->direccion);
        $docName = match($invoice->tipo_documento) { '01' => 'FACTURA', '03' => 'BOLETA', default => 'NOTA DE VENTA' };
        $t->center("** $docName **");
        $t->separator();
        $t->twoColumns('N°:', $invoice->full_number);
        $t->twoColumns('Fecha:', ($invoice->fecha_emision ?? now()->format('Y-m-d')) . ' ' . ($invoice->hora_emision ?? now()->format('H:i:s')));
        if ($invoice->customer) {
            $t->twoColumns('Cliente:', $invoice->customer->nombre ?? 'Varios');
            $t->twoColumns('Doc:', ($invoice->customer->documento_numero ?? ''));
        }
        $t->separator();

        foreach ($invoice->items as $item) {
            $t->itemLine("{$item->cantidad}x", $item->descripcion, 'S/ ' . number_format($item->precio_venta * $item->cantidad, 2));
        }
        $t->separator();
        $t->twoColumns('Subtotal:', 'S/ ' . number_format($invoice->subtotal, 2));
        $t->twoColumns('IGV:', 'S/ ' . number_format($invoice->igv, 2));
        $t->separator('=');
        $t->twoColumns('TOTAL:', 'S/ ' . number_format($invoice->total, 2));
        $t->blank();
        $t->twoColumns('Pago:', $invoice->metodo_pago ?? 'EFECTIVO');
        if ($invoice->referencia_pago) $t->twoColumns('Ref:', $invoice->referencia_pago);
        $t->separator();
        $t->center(now()->format('d/m/Y H:i:s'));
        $t->center('Gracias por su compra');
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    public static function cancelNotification($order, $item, string $format = 'text', string $dest = 'cocina'): string
    {
        $t = new self();
        $t->buildCancelHeader($order, $dest);
        $t->center('*** ANULADO ***');
        $t->separator();
        $t->itemLine("{$item->quantity}x", $item->product_name, '');
        $t->separator('=');
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    public static function cancelNotificationGrouped($order, string $format = 'text', string $dest = 'cocina'): string
    {
        $t = new self();
        $t->buildCancelHeader($order, $dest);
        $t->separator();
        foreach ($order->items as $item) {
            $t->itemLine("{$item->quantity}x", $item->product_name, '');
        }
        $t->separator('=');
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    protected function buildCancelHeader($order, string $dest = 'cocina'): void
    {
        $label = match($dest) {
            'cocina2' => 'COCINA 2',
            'bar' => 'BAR',
            default => 'COCINA 1',
        };
        $this->lines[] = self::DOUBLE_ON . self::BOLD_ON . '   ANULACION ' . $label . '   ' . self::BOLD_OFF . self::DOUBLE_OFF;
        $this->separator();
        $this->twoColumns('Pedido:', $order->order_number);
        $this->twoColumns('Mesa:', ($order->table->name ?? 'N/A') . ($order->table && $order->table->floor ? ' (' . $order->table->floor->name . ')' : ''));
        $this->twoColumns('Hora:', now()->format('H:i'));
        if ($order->user) $this->twoColumns('Mozo:', $order->user->name);
        if ($order->notes) $this->text('NOTA: ' . $order->notes);
        $this->separator();
    }

    public static function cashRegisterSummary($cashregister, array $data, string $format = 'text'): string
    {
        $t = new self();
        $company = \App\Models\Company::getMainCompany();
        $t->center($company->nombre_comercial ?? $company->razon_social ?? 'Restaurante');
        if ($company->ruc) $t->center('RUC: ' . $company->ruc);
        $t->center('** RESUMEN DE CAJA **');
        $t->separator();
        $t->twoColumns('Apertura:', $cashregister->fecha_apertura ? $cashregister->fecha_apertura->format('d/m/Y H:i') : '-');
        $t->twoColumns('Cierre:', $cashregister->fecha_cierre ? $cashregister->fecha_cierre->format('d/m/Y H:i') : now()->format('d/m/Y H:i'));
        $t->twoColumns('Apertura S/:', number_format($cashregister->monto_apertura, 2));
        $t->twoColumns('Cierre S/:', number_format($cashregister->monto_cierre ?? 0, 2));
        $t->separator();
        $t->center('RESUMEN POR DOCUMENTO');
        $t->twoColumns('Facturas:', $data['facturas']->count() . ' - S/ ' . number_format($data['facturas']->sum('total'), 2));
        $t->twoColumns('Boletas:', $data['boletas']->count() . ' - S/ ' . number_format($data['boletas']->sum('total'), 2));
        $t->twoColumns('Notas Venta:', $data['nvs']->count() . ' - S/ ' . number_format($data['nvs']->sum('total'), 2));
        $t->separator('=');
        $t->twoColumns('TOTAL:', 'S/ ' . number_format(collect($data['ventas'] ?? [])->sum('total'), 2));
        $t->separator();
        $t->center('POR MÉTODO DE PAGO');
        $calcEfectivo = 0; $calcTarjeta = 0; $calcYape = 0; $calcPlin = 0; $calcOtro = 0;
        foreach ($data['ventas'] ?? [] as $v) {
            $metodo = $v->metodo_pago ?? 'EFECTIVO';
            if (str_contains($metodo, ' + ')) {
                foreach (explode(' + ', $metodo) as $part) {
                    $part = trim($part);
                    $met = str_contains($part, '/') ? explode('/', $part)[0] : $part;
                    $amt = str_contains($part, '/') ? min((float) explode('/', $part)[1], (float) $v->total) : min((float) $v->total / count(explode(' + ', $metodo)), (float) $v->total);
                    match ($met) { 'EFECTIVO' => $calcEfectivo += $amt, 'TARJETA' => $calcTarjeta += $amt, 'YAPE' => $calcYape += $amt, 'PLIN' => $calcPlin += $amt, default => $calcOtro += $amt, };
                }
            } elseif (str_contains($metodo, '/')) {
                [$met, $amt] = explode('/', $metodo);
                $amt = min((float) $amt, (float) $v->total);
                match ($met) { 'EFECTIVO' => $calcEfectivo += $amt, 'TARJETA' => $calcTarjeta += $amt, 'YAPE' => $calcYape += $amt, 'PLIN' => $calcPlin += $amt, default => $calcOtro += $amt, };
            } else {
                match ($metodo) { 'EFECTIVO' => $calcEfectivo += (float) $v->total, 'TARJETA' => $calcTarjeta += (float) $v->total, 'YAPE' => $calcYape += (float) $v->total, 'PLIN' => $calcPlin += (float) $v->total, default => $calcOtro += (float) $v->total, };
            }
        }
        $t->twoColumns('Efectivo:', 'S/ ' . number_format($calcEfectivo, 2));
        $t->twoColumns('Tarjeta:', 'S/ ' . number_format($calcTarjeta, 2));
        $t->twoColumns('Yape:', 'S/ ' . number_format($calcYape, 2));
        $t->twoColumns('Plin:', 'S/ ' . number_format($calcPlin, 2));
        $t->twoColumns('Otro:', 'S/ ' . number_format($calcOtro, 2));
        if (isset($data['ventas']) && count($data['ventas']) > 0) {
            $t->separator();
            $t->center('LISTA DE COMPROBANTES');
            foreach ($data['ventas'] as $venta) {
                $cliente = $venta->customer->nombre ?? 'Varios';
                $t->text('  ' . $venta->full_number . ' - S/ ' . number_format($venta->total, 2));
                $t->text('    ' . $cliente . ' (' . ($venta->metodo_pago ?? 'EFECTIVO') . ')');
            }
        }
        if (isset($data['categoriasVentas']) && count($data['categoriasVentas']) > 0) {
            $t->separator();
            $t->center('POR CATEGORÍA');
            foreach ($data['categoriasVentas'] as $categoria => $info) {
                $t->itemLine((string)$info['cantidad'], $categoria, 'S/ ' . number_format($info['total'], 2));
            }
        }
        if (isset($data['productosVendidos']) && count($data['productosVendidos']) > 0) {
            $t->separator();
            $t->center('PRODUCTOS VENDIDOS');
            foreach ($data['productosVendidos'] as $producto => $info) {
                $t->itemLine((string)$info['cantidad'], $producto, 'S/ ' . number_format($info['total'], 2));
            }
        }
        if (isset($data['lineasEliminadas']) && count($data['lineasEliminadas']) > 0) {
            $t->separator();
            $t->center('LÍNEAS ELIMINADAS');
            foreach ($data['lineasEliminadas'] as $item) {
                $user = $item->cancelledBy->name ?? '';
                $t->text('x' . number_format($item->quantity, 0) . ' - ' . $item->product_name . ' - ' . $user . ' ' . ($item->cancelled_at ? $item->cancelled_at->format('H:i') : ''));
            }
        }
        $t->separator();
        $t->center(now()->format('d/m/Y H:i:s'));
        $t->center('Gracias por su preferencia');
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    protected function buildKitchenHeader($order, string $dest = 'cocina'): void
    {
        $label = match($dest) {
            'cocina2' => 'COCINA 2',
            'bar' => 'BAR',
            default => 'COCINA 1',
        };
        $this->center("*** $label ***");
        $this->separator();
        $this->twoColumns('Pedido:', $order->order_number);
        $this->twoColumns('Mesa:', ($order->table->name ?? 'N/A') . ($order->table && $order->table->floor ? ' (' . $order->table->floor->name . ')' : ''));
        $this->twoColumns('Hora:', now()->format('H:i'));
        if ($order->user) $this->twoColumns('Mozo:', $order->user->name);
        if ($order->notes) $this->text('NOTA: ' . $order->notes);
        $this->separator();
    }

    protected function buildPrebillHeader($order): void
    {
        $company = \App\Models\Company::getMainCompany();
        $this->center($company->nombre_comercial ?? $company->razon_social ?? 'Restaurante');
        if ($company->ruc) $this->center('RUC: ' . $company->ruc);
        $this->center('** PRECUENTA **');
        $this->separator();
        $this->twoColumns('Pedido:', $order->order_number);
        $this->twoColumns('Mesa:', ($order->table->name ?? 'N/A') . ($order->table && $order->table->floor ? ' (' . $order->table->floor->name . ')' : ''));
        $this->twoColumns('Hora:', now()->format('H:i'));
        if ($order->user) $this->twoColumns('Mozo:', $order->user->name);
        $this->separator();
    }
}
