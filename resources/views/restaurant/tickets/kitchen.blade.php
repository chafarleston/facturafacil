<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Cocina</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; }
        .ticket { padding: 5px; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .header h3 { font-size: 14px; }
        .info { margin-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; }
        .items { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin: 5px 0; }
        .item { margin-bottom: 3px; }
        .item-name { font-weight: bold; }
        .item-qty { display: flex; justify-content: space-between; }
        .notes { font-style: italic; color: #666; font-size: 10px; }
        .footer { text-align: center; margin-top: 5px; }
        .footer h4 { font-size: 16px; }
        .time { font-size: 10px; color: #666; }
        @media print {
            body { width: 80mm; }
            .ticket { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h3>*** PEDIDO COCINA ***</h3>
            <div>{{ $order->company->name ?? 'Restaurante' }}</div>
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
                <div class="item-qty">
                    <span>{{ $item->quantity }}x</span>
                </div>
                <div class="item-name">{{ $item->product_name }}</div>
                @if($item->notes)
                <div class="notes">Obs: {{ $item->notes }}</div>
                @endif
            </div>
            @endforeach
        </div>
        
        <div class="footer">
            <div class="time">{{ now()->format('d/m/Y H:i:s') }}</div>
            <h4>*** COCINA ***</h4>
        </div>
    </div>
    
    <script>window.print();</script>
</body>
</html>
