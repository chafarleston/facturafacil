<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $web['company_name'] }} · Pedido confirmado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $web['fontUrl'] }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e94560;
            --secondary: #3a1c4a;
            --bg: #f6f7fb;
            --surface: #ffffff;
            --surface-2: #f1f5f9;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --radius: 20px;
            --shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
            --brand-font: 'Poppins', sans-serif;
        }

        body[data-theme="minimal"] {
            --bg: #ffffff;
            --surface: #ffffff;
            --surface-2: #fafafa;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --radius: 14px;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        body[data-theme="elegant"] {
            --bg: #faf7f2;
            --surface: #ffffff;
            --surface-2: #f5efe7;
            --text: #2c241d;
            --muted: #8a7b6c;
            --border: #e7ded2;
            --radius: 16px;
        }

        body[data-theme="classic"] {
            --bg: #f4f6f8;
            --surface: #ffffff;
            --surface-2: #eef2f5;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #d1d5db;
            --radius: 12px;
        }

        body[data-theme="dark"] {
            --bg: #0f172a;
            --surface: #1e293b;
            --surface-2: #111827;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: #334155;
            --shadow: 0 24px 60px rgba(0, 0, 0, 0.4);
            color-scheme: dark;
        }

        body[data-theme="tropical"] {
            --bg: #fff7ed;
            --surface: #ffffff;
            --surface-2: #ffedd5;
            --text: #431407;
            --muted: #9a3412;
            --border: #fed7aa;
            --radius: 22px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            font-family: var(--brand-font);
            color: var(--text);
            background:
                radial-gradient(circle at 10% 0%, color-mix(in srgb, var(--primary) 18%, transparent), transparent 38%),
                radial-gradient(circle at 90% 100%, color-mix(in srgb, var(--secondary) 20%, transparent), transparent 42%),
                var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        .success-card {
            position: relative;
            width: min(520px, 100%);
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
            padding: 26px 22px 22px;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) + 6px);
            background: var(--surface);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .success-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 16px; }
        .brand img { height: 44px; max-width: 190px; object-fit: contain; }
        .brand span { font-size: 14px; font-weight: 600; color: var(--text); }

        .check {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 74px;
            height: 74px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: color-mix(in srgb, var(--primary) 14%, var(--surface));
            color: var(--primary);
            font-size: 36px;
        }

        h1 { font-size: 24px; line-height: 1.15; letter-spacing: -0.03em; margin-bottom: 4px; }
        .subtitle { color: var(--muted); font-size: 13px; line-height: 1.5; }

        .order-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 16px 0 2px;
            padding: 8px 16px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--primary) 10%, var(--surface));
            color: var(--primary);
            font-size: 23px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .total { color: var(--text); font-size: 20px; font-weight: 700; margin: 6px 0 16px; }

        .items {
            overflow: hidden;
            min-width: 0;
            margin-bottom: 12px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface-2);
        }

        .item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-width: 0;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            text-align: left;
        }
        .item-row:last-child { border-bottom: 0; }
        .item-row span:first-child { flex: 1; min-width: 0; color: var(--text); overflow-wrap: anywhere; }
        .item-row span:last-child { flex-shrink: 0; color: var(--muted); font-weight: 600; white-space: nowrap; }

        .delivery {
            min-width: 0;
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface-2);
            text-align: left;
            font-size: 13px;
            line-height: 1.55;
        }
        .delivery-title { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; color: var(--primary); font-weight: 700; font-size: 12px; }
        .delivery div { color: var(--muted); overflow-wrap: anywhere; }
        .delivery strong { color: var(--text); }

        .info {
            display: flex;
            flex-direction: column;
            gap: 7px;
            min-width: 0;
            margin: 14px 0 18px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }
        .info div { display: flex; align-items: flex-start; justify-content: center; gap: 7px; min-width: 0; }
        .info i { flex-shrink: 0; color: var(--primary); margin-top: 2px; }
        .info div > :not(i) { min-width: 0; overflow-wrap: anywhere; }

        .btn-new {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 48px;
            padding: 12px 16px;
            border-radius: 14px;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 10px 22px color-mix(in srgb, var(--primary) 26%, transparent);
        }

        .auto { margin-top: 12px; color: var(--muted); font-size: 11px; }

        @media (min-width: 600px) {
            .success-card { padding: 32px 30px 26px; }
            h1 { font-size: 28px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; }
        }
    </style>
</head>
<body
    data-theme="{{ $web['template'] }}"
    style="--primary: {{ $web['primary_color'] }}; --secondary: {{ $web['secondary_color'] }}; --brand-font: '{{ $web['font'] }}', sans-serif;"
>
    <div class="success-card">
        <div class="brand">
            @if($web['logoUrl'])
                <img src="{{ $web['logoUrl'] }}" alt="{{ $web['company_name'] }}">
            @endif
            <span>{{ $web['company_name'] }}</span>
        </div>

        <div class="check"><i class="fas fa-check"></i></div>
        <h1>Pedido confirmado</h1>
        <p class="subtitle">¡Gracias! Hemos recibido tu pedido.</p>

        <div class="order-number">{{ $order->order_number }}</div>
        <div class="total">S/ {{ number_format($order->total, 2) }}</div>

        <div class="items">
            @foreach($order->items as $item)
                <div class="item-row">
                    <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                    <span>S/ {{ number_format($item->total, 2) }}</span>
                </div>
            @endforeach
        </div>

        @if($order->delivery_type === 'web')
            <div class="delivery">
                <div class="delivery-title"><i class="fas fa-motorcycle"></i> Delivery</div>
                <div><strong>Cliente:</strong> {{ $order->customer_name }}</div>
                <div><strong>Dirección:</strong> {{ $order->delivery_address }}</div>
                @if($order->delivery_reference)
                    <div><strong>Referencia:</strong> {{ $order->delivery_reference }}</div>
                @endif
                <div><strong>Contacto:</strong> {{ $order->contact_number }}</div>
            </div>
        @endif

        <div class="info">
            <div><i class="fas fa-print"></i> {{ $successTexts['ticket'] }}</div>
            <div><i class="fas fa-cash-register"></i> {{ $successTexts['pay'] }}</div>
            <div><i class="fas fa-clock"></i> {{ $successTexts['prepare'] }}</div>
        </div>

        <a href="/autopedido" class="btn-new"><i class="fas fa-plus"></i> Nuevo pedido</a>
        <p class="auto">Volverás automáticamente en 30 segundos.</p>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = '/autopedido';
        }, 30000);
    </script>
</body>
</html>
