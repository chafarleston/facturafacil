<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Cocina</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        * { margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
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
        .header h3 { font-size: 12pt; margin-bottom: 1mm; }
        .header .company { font-size: 8pt; }
        .info { margin-bottom: 2mm; font-size: 9pt; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 1mm; }
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
            margin: 2mm 0;
        }
        .item { margin-bottom: 3mm; }
        .item-qty { font-weight: bold; font-size: 11pt; }
        .item-name { font-weight: bold; font-size: 10pt; }
        .notes { font-style: italic; color: #666; font-size: 8pt; margin-left: 2mm; }
        .footer { text-align: center; margin-top: 2mm; }
        .footer .separator { font-size: 11pt; margin: 1mm 0; }
        .time { font-size: 8pt; color: #666; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h3>** PEDIDO COCINA **</h3>
            <div class="company">{{ $order->company->name ?? 'Restaurante' }}</div>
        </div>
        
        <div class="info">
            <div class="info-row">
                <span>Pedido:</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="info-row">
                <span>Mesa:</span>
                <span>{{ $order->table->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span>Piso:</span>
                <span>{{ $order->table->floor->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span>Hora:</span>
                <span>{{ $order->created_at->format('H:i') }}</span>
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
            <div class="item">
                <div class="item-qty">{{ $item->quantity }}x</div>
                <div class="item-name">{{ $item->product_name }}</div>
                @if($item->notes)
                <div class="notes">Obs: {{ $item->notes }}</div>
                @endif
            </div>
            @endforeach
        </div>
        
        <div class="footer">
            <div class="time">{{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="separator">**** COCINA ****</div>
        </div>
    </div>
</body>
</html>
