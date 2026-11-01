<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>Precuenta · {{ $table->name }} · {{ $web['company_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $web['fontUrl'] }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: {{ $web['primary_color'] }};
            --secondary: {{ $web['secondary_color'] }};
            --bg: #f4f5f8;
            --surface: #ffffff;
            --surface-2: #f7f8fa;
            --text: #1f2430;
            --muted: #7b8190;
            --border: #e8eaf0;
            --radius: 22px;
            --radius-sm: 12px;
            --shadow: 0 18px 50px rgba(20, 25, 40, .10);
            --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
            --banner-color: #fff;
            --banner-border: 0;
            --banner-letter: 3px;
            --banner-shadow: 0 8px 20px rgba(0, 0, 0, .12);
            --card-border: 1px solid var(--border);
            --head-border: 1px dashed var(--border);
            --grand-color: var(--primary);
            --button-bg: var(--primary);
            --button-color: #fff;
            --brand-transform: none;
            --body-image: radial-gradient(circle at 15% 0%, rgba(0, 0, 0, .035), transparent 42%);
            --font: '{{ $web['font'] }}', sans-serif;
        }

        body[data-theme="elegant"] {
            --bg: #f8f5ef;
            --surface: #ffffff;
            --surface-2: #faf7f1;
            --text: #241f19;
            --muted: #7b7164;
            --border: #e7dfd2;
            --radius: 3px;
            --radius-sm: 2px;
            --shadow: 0 14px 34px rgba(60, 45, 20, .07);
            --banner-bg: transparent;
            --banner-color: var(--primary);
            --banner-border: 1px solid #d9cbb7;
            --banner-letter: 6px;
            --banner-shadow: none;
            --card-border: 1px solid #d9cbb7;
            --grand-color: var(--primary);
            --button-bg: #241f19;
            --brand-transform: uppercase;
            --body-image: none;
        }

        body[data-theme="minimal"] {
            --bg: #f7f7f7;
            --surface: #ffffff;
            --surface-2: #fafafa;
            --text: #111111;
            --muted: #666666;
            --border: #e5e5e5;
            --radius: 0;
            --radius-sm: 0;
            --shadow: none;
            --banner-bg: #111111;
            --banner-color: #ffffff;
            --banner-border: 0;
            --banner-letter: 4px;
            --banner-shadow: none;
            --card-border: 1px solid #dddddd;
            --head-border: 1px solid #eeeeee;
            --grand-color: #111111;
            --button-bg: #111111;
            --brand-transform: none;
            --body-image: none;
        }

        body[data-theme="classic"] {
            --bg: #f3ede2;
            --surface: #ffffff;
            --surface-2: #fbf7f0;
            --text: #2f2a22;
            --muted: #7b6f5e;
            --border: #d8cdbc;
            --radius: 0;
            --radius-sm: 0;
            --shadow: 0 12px 28px rgba(60, 45, 20, .08);
            --banner-bg: var(--secondary);
            --banner-color: #ffffff;
            --banner-border: 0;
            --banner-letter: 4px;
            --banner-shadow: none;
            --card-border: 1px solid #d8cdbc;
            --head-border: 1px solid #e3d9c9;
            --grand-color: var(--primary);
            --button-bg: #2f2a22;
            --brand-transform: uppercase;
            --body-image: none;
        }

        body[data-theme="dark"] {
            --bg: #0b0d12;
            --surface: #151923;
            --surface-2: #11151e;
            --text: #e9e9f2;
            --muted: #9aa3b2;
            --border: #242a38;
            --radius: 18px;
            --radius-sm: 12px;
            --shadow: 0 22px 60px rgba(0, 0, 0, .45);
            --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
            --banner-color: #ffffff;
            --banner-border: 0;
            --banner-letter: 3px;
            --banner-shadow: 0 8px 22px rgba(0, 0, 0, .35);
            --card-border: 1px solid #242a38;
            --head-border: 1px dashed #2a3142;
            --grand-color: #ffffff;
            --button-bg: linear-gradient(135deg, var(--primary), var(--secondary));
            --brand-transform: none;
            --body-image: radial-gradient(circle at 15% 0%, rgba(255, 255, 255, .05), transparent 42%);
            color-scheme: dark;
        }

        body[data-theme="tropical"] {
            --bg: #fffaf2;
            --surface: #ffffff;
            --surface-2: #fff4e6;
            --text: #12332e;
            --muted: #6b7f7a;
            --border: #f0e2cc;
            --radius: 26px;
            --radius-sm: 16px;
            --shadow: 0 18px 44px rgba(255, 122, 89, .13);
            --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
            --banner-color: #ffffff;
            --banner-border: 0;
            --banner-letter: 2px;
            --banner-shadow: 0 8px 22px rgba(255, 122, 89, .20);
            --card-border: 1px solid #f0e2cc;
            --head-border: 1px dashed #eadbc5;
            --grand-color: var(--primary);
            --button-bg: linear-gradient(135deg, var(--primary), var(--secondary));
            --brand-transform: none;
            --body-image: radial-gradient(circle at 90% 100%, rgba(0, 184, 148, .12), transparent 40%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            padding: 18px;
            font-family: var(--font);
            color: var(--text);
            background: var(--body-image), var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        .card {
            width: min(560px, 100%);
            margin: 0 auto;
            overflow: hidden;
            border: var(--card-border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .head {
            padding: 22px 20px 16px;
            border-bottom: var(--head-border);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            text-align: left;
        }

        .brand img {
            max-width: 220px;
            width: auto;
            object-fit: contain;
        }

        .company {
            color: var(--text);
            font-size: 20px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: .02em;
            text-transform: var(--brand-transform);
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 4px 12px;
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .meta span { display: inline-flex; align-items: center; gap: 4px; }
        .meta i { color: var(--primary); }

        .prebill-banner {
            margin: 16px 18px 0;
            padding: 9px 12px;
            border: var(--banner-border);
            border-radius: var(--radius-sm);
            background: var(--banner-bg);
            box-shadow: var(--banner-shadow);
            color: var(--banner-color);
            text-align: center;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: var(--banner-letter);
        }

        .info {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin: 14px 18px 0;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface-2);
        }

        .info .cell { min-width: 0; text-align: center; }
        .info .lbl {
            display: block;
            margin-bottom: 3px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .7px;
            text-transform: uppercase;
        }
        .info strong { font-size: 14px; }
        .info small { color: var(--muted); font-size: 11px; }

        .items { padding: 4px 18px 0; }

        .item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .item:last-child { border-bottom: 0; }

        .item-main { min-width: 0; }
        .item .name { font-size: 14px; font-weight: 700; line-height: 1.35; overflow-wrap: anywhere; }
        .item .detail { margin-top: 2px; color: var(--muted); font-size: 12px; }
        .item .notes { margin-top: 3px; color: var(--muted); font-size: 11px; font-style: italic; overflow-wrap: anywhere; }
        .item .amount { flex-shrink: 0; color: var(--text); font-size: 14px; font-weight: 800; white-space: nowrap; }

        .empty { padding: 26px 18px; color: var(--muted); text-align: center; font-size: 14px; }

        .totals {
            margin: 8px 18px 0;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface-2);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 4px 0;
            color: var(--muted);
            font-size: 13px;
        }

        .total-row span:last-child { color: var(--text); font-weight: 600; }
        .total-row.grand {
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
            color: var(--text);
            font-size: 20px;
            font-weight: 800;
        }
        .total-row.grand span:last-child { color: var(--grand-color); font-size: 22px; }

        .footer {
            padding: 16px 18px 8px;
            color: var(--muted);
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
        }
        .footer .disclaimer { font-style: italic; }
        .footer .thanks { margin-top: 8px; color: var(--text); font-weight: 600; }

        .no-order { padding: 34px 20px; text-align: center; }
        .no-order i { display: block; margin-bottom: 12px; color: var(--muted); opacity: .55; font-size: 42px; }
        .no-order h2 { margin-bottom: 6px; color: var(--text); font-size: 19px; }
        .no-order p { margin-bottom: 18px; color: var(--muted); font-size: 13px; line-height: 1.5; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 42px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            background: var(--button-bg);
            color: var(--button-color);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .updated {
            padding: 10px 18px 16px;
            color: var(--muted);
            text-align: center;
            font-size: 10px;
        }

        @media (max-width: 520px) {
            body { padding: 10px; }
            .head { padding: 18px 14px 14px; }
            .brand { gap: 10px; }
            .company { font-size: 17px; }
            .prebill-banner { margin-left: 14px; margin-right: 14px; }
            .info { grid-template-columns: 1fr; gap: 10px; margin-left: 14px; margin-right: 14px; }
            .info .cell {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding-bottom: 6px;
                border-bottom: 1px dashed var(--border);
                text-align: left;
            }
            .info .cell:last-child { padding-bottom: 0; border-bottom: 0; }
            .info .lbl { margin-bottom: 0; }
            .items { padding-left: 14px; padding-right: 14px; }
            .totals { margin-left: 14px; margin-right: 14px; }
            .footer { padding-left: 14px; padding-right: 14px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body data-theme="{{ $web['template'] }}">
    <main class="card">
        <header class="head">
            <div class="brand">
                @if($web['logoUrl'])
                    <img src="{{ $web['logoUrl'] }}" alt="{{ $web['company_name'] }}" style="height: {{ $web['logo_height'] }}px;">
                @endif
                <div>
                    <div class="company">{{ $web['company_name'] }}</div>
                    <div class="meta">
                        @if($company?->ruc)<span><i class="fas fa-id-card"></i> RUC: {{ $company->ruc }}</span>@endif
                        @if($company?->direccion)<span><i class="fas fa-location-dot"></i> {{ $company->direccion }}</span>@endif
                        @if($company?->telefono)<span><i class="fas fa-phone"></i> {{ $company->telefono }}</span>@endif
                        @if($company?->email)<span><i class="fas fa-envelope"></i> {{ $company->email }}</span>@endif
                    </div>
                </div>
            </div>
        </header>

        <div class="prebill-banner">Precuenta</div>

        @if($order)
            <div class="info">
                <div class="cell">
                    <span class="lbl">Mesa</span>
                    <strong>{{ $table->name }}</strong>
                    @if($table->floor)<small>({{ $table->floor->name }})</small>@endif
                </div>
                <div class="cell">
                    <span class="lbl">Pedido</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
                <div class="cell">
                    <span class="lbl">Hora</span>
                    <strong>{{ $order->created_at?->format('H:i') ?? now()->format('H:i') }}</strong>
                </div>
            </div>

            <div class="items">
                @php
                    $visibleItems = $order->items->filter(fn ($i) => $i->kitchen_status !== 'CANCELLED' && !$i->paid_invoice_id);
                @endphp
                @forelse($visibleItems as $item)
                    <div class="item">
                        <div class="item-main">
                            <div class="name">{{ $item->product_name }}</div>
                            <div class="detail">{{ number_format($item->quantity, 2) }} x S/ {{ number_format($item->unit_price, 2) }}</div>
                            @if($item->notes)<div class="notes">{{ $item->notes }}</div>@endif
                        </div>
                        <div class="amount">S/ {{ number_format($item->total, 2) }}</div>
                    </div>
                @empty
                    <div class="empty">No hay consumos pendientes.</div>
                @endforelse
            </div>

            <div class="totals">
                <div class="total-row"><span>Subtotal:</span><span>S/ {{ number_format($order->subtotal, 2) }}</span></div>
                <div class="total-row"><span>IGV ({{ $company?->getActiveIgvPercent() ?? 18 }}%):</span><span>S/ {{ number_format($order->igv, 2) }}</span></div>
                <div class="total-row grand"><span>TOTAL:</span><span>S/ {{ number_format($order->total, 2) }}</span></div>
            </div>

            <div class="footer">
                <div class="disclaimer">Esto no es un comprobante de venta, si desea pedir boleta o factura escriba sus datos.</div>
                <div class="thanks">Gracias por su visita</div>
            </div>
        @else
            <div class="no-order">
                <i class="fas fa-receipt"></i>
                <h2>Mesa sin pedido activo</h2>
                <p>La mesa <strong>{{ $table->name }}</strong> no tiene consumos pendientes por ahora.</p>
                <a href="{{ url()->current() }}" class="btn"><i class="fas fa-sync-alt"></i> Actualizar</a>
            </div>
        @endif

        <div class="updated">Actualizado: {{ now()->format('d/m/Y H:i:s') }} · se actualiza automáticamente</div>
    </main>
</body>
</html>
