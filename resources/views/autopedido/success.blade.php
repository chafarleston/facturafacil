<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Pedido Confirmado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; color: #333; border-radius: 20px; padding: 40px 30px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,.3); }
        .check { font-size: 60px; color: #28a745; margin-bottom: 15px; }
        .order-num { font-size: 48px; font-weight: bold; color: #e94560; margin: 10px 0; }
        .total { font-size: 24px; color: #28a745; font-weight: bold; margin: 15px 0; }
        .info { color: #666; margin: 10px 0; font-size: 14px; line-height: 1.5; }
        .btn-new { display: block; width: 100%; padding: 16px; margin-top: 20px; background: #e94560; color: #fff; border: none; border-radius: 12px; font-size: 18px; font-weight: bold; cursor: pointer; text-decoration: none; }
        .items { text-align: left; margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 8px; }
        .item-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="check"><i class="fas fa-check-circle"></i></div>
        <h2>Pedido Confirmado</h2>
        <div class="order-num">{{ $order->order_number }}</div>
        <div class="total">S/ {{ number_format($order->total, 2) }}</div>

        <div class="items">
            @foreach($order->items as $item)
            <div class="item-row">
                <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                <span>S/ {{ number_format($item->total, 2) }}</span>
            </div>
            @endforeach
        </div>

        <div class="info">
            <i class="fas fa-print"></i> Su ticket se está imprimiendo<br>
            <i class="fas fa-cash-register"></i> Pase a caja para pagar<br>
            <i class="fas fa-clock"></i> Su pedido comenzará a prepararse después del pago
        </div>

        <a href="{{ route('autopedido.index') }}" class="btn-new"><i class="fas fa-plus"></i> Nuevo Pedido</a>
    </div>

    <script>
        setTimeout(function() { window.location.href = '{{ route("autopedido.index") }}'; }, 30000);
    </script>
</body>
</html>
