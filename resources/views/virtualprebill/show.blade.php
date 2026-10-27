<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>Precuenta - {{ $table->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f4f5f8; color: #333; min-height: 100vh; padding: 16px; }
        .card { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,.08); overflow: hidden; }
        .head { text-align: center; padding: 22px 18px 16px; border-bottom: 1px dashed #d9d9d9; }
        .head img { max-height: 54px; margin-bottom: 8px; }
        .head .company { font-size: 18px; font-weight: 700; text-transform: uppercase; }
        .head .meta { font-size: 12px; color: #777; line-height: 1.6; margin-top: 4px; }
        .banner { background: #e94560; color: #fff; text-align: center; font-weight: 700; letter-spacing: 2px; padding: 8px; font-size: 13px; }
        .info { display: flex; justify-content: space-between; padding: 12px 18px; font-size: 13px; border-bottom: 1px solid #eee; gap: 10px; }
        .info div { text-align: center; flex: 1; }
        .info .lbl { color: #999; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        .items { padding: 6px 18px; }
        .item { display: flex; justify-content: space-between; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .item:last-child { border-bottom: none; }
        .item .name { font-weight: 600; font-size: 14px; }
        .item .detail { font-size: 12px; color: #777; margin-top: 2px; }
        .item .notes { font-size: 11px; color: #999; font-style: italic; margin-top: 2px; }
        .item .amount { font-weight: 700; font-size: 14px; white-space: nowrap; }
        .empty { text-align: center; color: #999; padding: 24px 18px; font-size: 14px; }
        .totals { padding: 14px 18px; border-top: 1px dashed #d9d9d9; }
        .total-row { display: flex; justify-content: space-between; font-size: 13px; color: #555; margin-bottom: 6px; }
        .total-row.grand { font-size: 20px; font-weight: 800; color: #222; margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd; }
        .footer { text-align: center; padding: 16px 18px 22px; font-size: 11px; color: #888; line-height: 1.6; }
        .footer .disclaimer { font-style: italic; }
        .actions { text-align: center; padding: 0 18px 22px; }
        .btn { display: inline-block; padding: 10px 18px; border-radius: 10px; background: #e94560; color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; }
        .no-order { text-align: center; padding: 40px 22px; }
        .no-order i { font-size: 46px; color: #d0d0d0; margin-bottom: 14px; display: block; }
        .no-order h2 { font-size: 18px; margin-bottom: 6px; }
        .no-order p { color: #888; font-size: 13px; margin-bottom: 18px; }
        .updated { text-align: center; font-size: 10px; color: #b0b0b0; padding-bottom: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            @if($company && $company->getLogoUrl())
                <img src="{{ $company->getLogoUrl() }}" alt="{{ $company->nombre_comercial }}">
            @endif
            <div class="company">{{ $company?->nombre_comercial ?: ($company?->razon_social ?? 'Restaurante') }}</div>
            <div class="meta">
                @if($company?->ruc) RUC: {{ $company->ruc }}<br> @endif
                @if($company?->direccion) {{ $company->direccion }}<br> @endif
                @if($company?->telefono) Tel: {{ $company->telefono }} @endif
                @if($company?->email) · {{ $company->email }} @endif
            </div>
        </div>

        <div class="banner">PRECUENTA</div>

        @if($order)
            <div class="info">
                <div>
                    <div class="lbl">Mesa</div>
                    <div><strong>{{ $table->name }}</strong>@if($table->floor) ({{ $table->floor->name }})@endif</div>
                </div>
                <div>
                    <div class="lbl">Pedido</div>
                    <div>{{ $order->order_number }}</div>
                </div>
                <div>
                    <div class="lbl">Hora</div>
                    <div>{{ $order->created_at?->format('H:i') ?? now()->format('H:i') }}</div>
                </div>
            </div>

            <div class="items">
                @php
                    $visibleItems = $order->items->filter(fn ($i) => $i->kitchen_status !== 'CANCELLED' && !$i->paid_invoice_id);
                @endphp
                @forelse($visibleItems as $item)
                    <div class="item">
                        <div>
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
                <div style="margin-top:8px;">Gracias por su visita</div>
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
    </div>
</body>
</html>
