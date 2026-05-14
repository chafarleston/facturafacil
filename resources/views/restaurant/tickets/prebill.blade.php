<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Precuenta</title>
    <style>
        @page { margin: 0; size: 80mm auto; }
        * { margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            width: 75mm;
            margin: 0 auto;
            padding: 3mm;
        }
        .ticket { width: 100%; }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        .header h3 { font-size: 14pt; margin-bottom: 1mm; }
        .header .company { font-size: 8pt; text-transform: uppercase; }
        .header .subtitle { font-size: 11pt; margin-top: 2mm; letter-spacing: 2px; }
        .info { margin-bottom: 2mm; font-size: 8pt; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 1mm; }
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
            margin: 2mm 0;
        }
        .item-row { margin-bottom: 2mm; }
        .item-desc { font-size: 9pt; font-weight: bold; }
        .item-detail { font-size: 8pt; display: flex; justify-content: space-between; }
        .item-notes { font-style: italic; color: #666; font-size: 7pt; margin-left: 2mm; }
        .totals { margin: 2mm 0; }
        .total-row { display: flex; justify-content: space-between; font-size: 9pt; margin-bottom: 1mm; }
        .total-row.grand { font-size: 12pt; font-weight: bold; border-top: 1px solid #000; padding-top: 1mm; margin-top: 1mm; }
        .footer { text-align: center; margin-top: 2mm; font-size: 8pt; }
        .footer .separator { font-size: 10pt; margin: 1mm 0; letter-spacing: 1px; }
        .time { font-size: 7pt; color: #666; }
        .dotted-line { border-top: 1px dashed #ccc; margin: 2mm 0; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <div class="company">{{ $company->name ?? 'Restaurante' }}</div>
            <div>{{ $company->ruc ?? '' }}</div>
            <div>{{ $company->direccion ?? '' }}</div>
            <div class="subtitle">** PRECUENTA **</div>
        </div>
        
        <div class="info">
            <div class="info-row">
                <span>Pedido:</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="info-row">
                <span>Mesa:</span>
                <span>{{ $order->table->name ?? 'N/A' }} @if($order->table && $order->table->floor) ({{ $order->table->floor->name }}) @endif</span>
            </div>
            <div class="info-row">
                <span>Hora:</span>
                <span>{{ now()->format('H:i') }}</span>
            </div>
            @if($order->user)
            <div class="info-row">
                <span>Mozo:</span>
                <span>{{ $order->user->name }}</span>
            </div>
            @endif
        </div>
        
        <div class="items">
            @foreach($order->items as $item)
            <div class="item-row">
                <div class="item-desc">{{ $item->product_name }}</div>
                <div class="item-detail">
                    <span>{{ number_format($item->quantity, 2) }} x S/ {{ number_format($item->unit_price, 2) }}</span>
                    <span>S/ {{ number_format($item->total, 2) }}</span>
                </div>
                @if($item->notes)
                <div class="item-notes">{{ $item->notes }}</div>
                @endif
            </div>
            @endforeach
        </div>
        
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>S/ {{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="total-row">
                <span>IGV (18%):</span>
                <span>S/ {{ number_format($order->igv, 2) }}</span>
            </div>
            <div class="total-row grand">
                <span>TOTAL:</span>
                <span>S/ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
        
        <div class="dotted-line"></div>
        
        <div class="footer">
            <div class="time">{{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="separator">**** PRECUENTA ****</div>
            <div style="margin-top:1mm;">Gracias por su visita</div>
        </div>
    </div>
</body>
</html>
