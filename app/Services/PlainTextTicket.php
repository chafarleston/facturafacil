<?PHP
namespace App\Services;

class PlainTextTicket
{
    private $text = '';
    private $format = 'text';
    
    public function __construct(string $format = 'text')
    {
        $this->format = $format;
    }
    
    public function center(string $text, string $char = ' '): void
    {
        $pad = intval((32 - strlen($this->clean($text))) / 2);
        if ($pad < 0) $pad = 0;
        $this->text .= str_repeat($char, $pad) . $text . "\n";
    }
    
    public function left(string $text): void
    {
        $this->text .= $text . "\n";
    }
    
    public function right(string $text): void
    {
        $clean = $this->clean($text);
        $pad = 32 - strlen($clean);
        if ($pad < 0) $pad = 0;
        $this->text .= str_repeat(' ', $pad) . $text . "\n";
    }
    
    public function twoColumns(string $left, string $right, string $glue = ' '): void
    {
        $cleanL = $this->clean($left);
        $cleanR = $this->clean($right);
        $dots = 32 - strlen($cleanL) - strlen($cleanR);
        if ($dots < 1) $dots = 1;
        $this->text .= $left . str_repeat($glue, $dots) . $right . "\n";
    }
    
    public function itemLine(string $qty, string $name, string $total): void
    {
        $line = $qty . ' ' . $name;
        $clean = $this->clean($line);
        $pad = 32 - strlen($clean) - strlen($this->clean($total));
        if ($pad < 0) { $line = substr($line, 0, $pad - 3) . '...'; $pad = 1; }
        $this->text .= $line . str_repeat(' ', $pad) . $total . "\n";
    }
    
    public function separator(string $char = '-'): void
    {
        $this->text .= str_repeat($char, 32) . "\n";
    }
    
    public function blank(): void
    {
        $this->text .= "\n";
    }
    
    public function text(string $text): void
    {
        $this->left($text);
    }
    
    public function getText(): string
    {
        return $this->text;
    }
    
    public function getEscPos(): string
    {
        $lines = explode("\n", $this->text);
        $out = "\x1B\x40"; // INIT
        $out .= "\x1B\x74\x02"; // CP850
        foreach ($lines as $line) {
            $trimmed = rtrim($line, " \t\r\n");
            $encoded = $this->utf8ToCp850($trimmed);
            $out .= $encoded . "\x0A";
        }
        $out .= "\x1B\x64\x05"; // FEED 5
        $out .= "\x1D\x56\x00"; // CUT
        return $out;
    }
    
    private function utf8ToCp850(string $text): string
    {
        $map = [
            'á' => "\xA0", 'é' => "\x82", 'í' => "\xA1", 'ó' => "\xA2", 'ú' => "\xA3",
            'Á' => "\xB5", 'É' => "\x90", 'Í' => "\xD6", 'Ó' => "\xE0", 'Ú' => "\xE9",
            'ñ' => "\xA4", 'Ñ' => "\xA5", 'ü' => "\x81", 'Ü' => "\x9A",
            '¡' => "\xA6", '¿' => "\xA8",
            '°' => "\xF8", '¬' => "\xAA",
        ];
        return strtr($text, $map);
    }
    
    protected function clean(string $text): string
    {
        $clean = strtr($text, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
            'ñ'=>'n','Ñ'=>'N','ü'=>'u','Ü'=>'U',
        ]);
        return $clean;
    }
    
    protected function buildQR(): string
    {
        return '';
    }
    
    public function setQR(string $data): void
    {
    }
    
    public static function kitchenTicket($order, string $format = 'text', string $dest = 'cocina'): string
    {
        $t = new self($format);
        $t->buildKitchenHeader($order, $dest);
        $t->separator();
        $items = $order->items ?? $order->pendingItems ?? [];
        foreach ($items as $item) {
            if ($item->kitchen_status === 'CANCELLED') continue;
            $t->itemLine(number_format($item->quantity, $item->quantity == intval($item->quantity) ? 0 : 2), $item->product_name, '');
            if ($item->notes) $t->text('    Nota: ' . $item->notes);
            if ($item->auxiliary_items) {
                $names = \App\Models\AuxiliaryItem::whereIn('id', $item->auxiliary_items)->pluck('name')->toArray();
                if ($names) $t->text('    + ' . implode(', ', $names));
            }
        }
        $t->separator();
        $t->text('Hora: ' . now()->format('H:i:s'));
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }
    
    public static function prebillTicket($order, string $format = 'text'): string
    {
        $t = new self($format);
        $t->buildPrebillHeader($order);
        $t->separator();
        foreach ($order->items as $item) {
            if ($item->kitchen_status === 'CANCELLED') continue;
            $totalItem = $item->unit_price * $item->quantity;
            $t->itemLine(number_format($item->quantity, 0), $item->product_name, 'S/ ' . number_format($totalItem, 2));
        }
        $t->separator();
        $t->twoColumns('SUBTOTAL:', 'S/ ' . number_format($order->subtotal ?? $order->total, 2));
        $igvPercent = $order->igvPercent ?? 18;
        $t->twoColumns('IGV (' . $igvPercent . '%):', 'S/ ' . number_format($order->igv ?? 0, 2));
        $t->twoColumns('TOTAL:', 'S/ ' . number_format($order->total, 2));
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }
    
    public static function invoiceTicket($invoice, string $format = 'text'): string
    {
        return ''; // Use Greenter PDF instead
    }
    
    public static function cancelNotification($order, $item, string $format = 'text', string $dest = 'cocina'): string
    {
        $t = new self($format);
        $t->center('*** ANULACIÓN ***', '*');
        $t->blank();
        $t->text('Pedido: ' . $order->order_number);
        $t->text('Producto: ' . $item->product_name);
        $t->text('Cantidad: ' . $item->quantity);
        $t->text('Anulado por: ' . ($item->cancelledBy->name ?? 'Usuario'));
        $t->blank();
        $t->separator();
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }
    
    public static function cancelNotificationGrouped($order, string $format = 'text', string $dest = 'cocina'): string
    {
        $t = new self($format);
        $t->buildCancelHeader($order, $dest);
        $items = $order->items->where('kds_destination', $dest);
        foreach ($items as $item) {
            $t->itemLine(number_format($item->quantity, 0), $item->product_name, '');
        }
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }
    
    protected function buildCancelHeader($order, string $dest = 'cocina'): void
    {
        $this->center('*** ANULACIÓN COCINA ***', '*');
        $this->blank();
        $this->text('Pedido: ' . $order->order_number);
        if ($order->order_type === 'kiosko') {
            $this->text('Autoservicio');
        } elseif ($order->table) {
            $this->text('Mesa: ' . $order->table->name);
        }
        $this->text('Hora: ' . now()->format('H:i:s'));
    }
    
    public static function cashRegisterSummary($cashregister, array $data, string $format = 'text'): string
    {
        $t = new self($format);
        $t->center('*** CIERRE DE CAJA ***', '*');
        $t->blank();
        $t->text('Caja #' . $cashregister->id);
        $t->text('Apertura: ' . ($cashregister->fecha_apertura ? $cashregister->fecha_apertura->format('d/m H:i') : ''));
        $t->text('Cierre: ' . now()->format('d/m H:i'));
        $t->separator();
        $t->center('RESUMEN POR DOCUMENTO');
        $facturas = $data['facturas'] ?? collect();
        $boletas = $data['boletas'] ?? collect();
        $nvs = $data['nvs'] ?? collect();
        if ($facturas->count() > 0) $t->twoColumns('Facturas:', $facturas->count() . ' und - S/ ' . number_format($facturas->sum('total'), 2));
        if ($boletas->count() > 0) $t->twoColumns('Boletas:', $boletas->count() . ' und - S/ ' . number_format($boletas->sum('total'), 2));
        if ($nvs->count() > 0) $t->twoColumns('Notas Venta:', $nvs->count() . ' und - S/ ' . number_format($nvs->sum('total'), 2));
        $t->separator();
        $t->twoColumns('Total Ventas:', 'S/ ' . number_format($data['total_ventas'] ?? 0, 2));
        $t->separator();
        $t->center('POR METODO DE PAGO');
        foreach (['efectivo','tarjeta','yape','plin','otro'] as $met) {
            $label = ucfirst($met);
            $amount = $data[$met] ?? 0;
            if ($amount > 0) $t->twoColumns($label . ':', 'S/ ' . number_format($amount, 2));
        }
        $t->separator();

        $ventas = $data['ventas'] ?? collect();
        if ($ventas->count() > 0) {
            $t->center('COMPROBANTES');
            foreach ($ventas as $venta) {
                $full = $venta->full_number ?? '';
                $total = number_format($venta->total, 2);
                $t->text($full . ' - S/ ' . $total);
            }
            $t->separator();
        }

        $categorias = $data['categoriasVentas'] ?? [];
        if (count($categorias) > 0) {
            $t->center('POR CATEGORIA');
            foreach ($categorias as $cat => $d) {
                $t->twoColumns($d['cantidad'] . 'x ' . $cat, 'S/ ' . number_format($d['total'], 2));
            }
            $t->separator();
        }

        $productos = $data['productosVendidos'] ?? [];
        if (count($productos) > 0) {
            $t->center('PRODUCTOS VENDIDOS');
            foreach ($productos as $prod => $d) {
                $t->twoColumns($d['cantidad'] . 'x ' . $prod, 'S/ ' . number_format($d['total'], 2));
            }
            $t->separator();
        }

        $lineas = $data['lineasEliminadas'] ?? collect();
        if ($lineas->count() > 0) {
            $t->center('LINEAS ELIMINADAS');
            foreach ($lineas as $item) {
                $t->text('x' . number_format($item->quantity, 0) . ' - ' . $item->product_name);
            }
            $t->separator();
        }

        $t->text('Monto apertura: S/ ' . number_format($cashregister->monto_apertura ?? 0, 2));
        $t->text('Monto cierre: S/ ' . number_format($cashregister->monto_cierre ?? 0, 2));
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    public static function autoPedidoTicket($order, string $format = 'text'): string
    {
        $t = new self($format);
        $t->center('*** AUTO PEDIDO ***', '*');
        $t->center('FacturaFacil');
        $t->blank();
        $t->center($order->order_number, ' ');
        $t->separator();
        foreach ($order->items as $item) {
            $t->itemLine(number_format($item->quantity, 0), $item->product_name, 'S/ ' . number_format($item->total, 2));
        }
        $t->separator();
        $t->twoColumns('TOTAL:', 'S/ ' . number_format($order->total, 2));
        $t->blank();
        $t->center('Pase a Caja para pagar');
        $t->center('Gracias por su pedido!');
        $t->blank();
        $t->text('Fecha: ' . now()->format('d/m/Y H:i'));
        return $format === 'escpos' ? $t->getEscPos() : $t->getText();
    }

    protected function buildKitchenHeader($order, string $dest = 'cocina'): void
    {
        $label = match($dest) { 'cocina2' => 'COCINA 2', 'bar' => 'BAR', default => 'COCINA' };
        $this->center('*** ' . $label . ' ***', '*');
        $this->blank();
        $this->text('Pedido: ' . $order->order_number);
        if ($order->order_type === 'kiosko') {
            $this->text('Autoservicio');
        } elseif ($order->table) {
            $this->text('Mesa: ' . $order->table->name);
        }
        if ($order->user) $this->text('Mozo: ' . $order->user->name);
        $this->text('Hora: ' . now()->format('H:i:s'));
    }
    
    protected function buildPrebillHeader($order): void
    {
        $this->center('*** PRECUENTA ***', '*');
        $this->blank();
        $this->text('Pedido: ' . $order->order_number);
        if ($order->order_type === 'kiosko') {
            $this->text('Autoservicio');
        } elseif ($order->table) {
            $this->text('Mesa: ' . $order->table->name);
        }
        $this->text('Hora: ' . now()->format('H:i:s'));
    }
}
