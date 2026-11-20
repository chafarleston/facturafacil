<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $preventa->numero }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #222; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 14px; }
        .company { font-size: 16px; font-weight: bold; margin: 0; }
        .muted { color: #666; }
        .title { font-size: 18px; font-weight: bold; text-align: center; margin: 10px 0; }
        .row { width: 100%; }
        .col { width: 50%; vertical-align: top; }
        .box { border: 1px solid #ccc; padding: 8px; margin-bottom: 10px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #333; color: #fff; padding: 6px; border: 1px solid #333; text-align: left; }
        table.items td { padding: 5px 6px; border: 1px solid #ccc; }
        table.items tr:nth-child(even) td { background: #f7f7f7; }
        .text-right { text-align: right; }
        .totals { width: 45%; margin-left: 55%; margin-top: 10px; }
        .totals td { padding: 4px 6px; }
        .total { font-weight: bold; font-size: 12px; border-top: 1px solid #333; }
        .footer { margin-top: 30px; font-size: 8px; color: #777; text-align: center; }
        .signature { margin-top: 45px; width: 45%; border-top: 1px solid #333; padding-top: 4px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table class="row"><tr>
            <td class="col">
                @if($logoBase64)
                    <div style="margin-bottom: 6px;">
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 70px; max-width: 160px;">
                    </div>
                @endif
                <p class="company">{{ $company->nombre_comercial ?: $company->razon_social }}</p>
                <div class="muted">RUC: {{ $company->ruc }}</div>
                @if($company->direccion)<div class="muted">{{ $company->direccion }}</div>@endif
                @if($company->telefono)<div class="muted">Tel: {{ $company->telefono }}</div>@endif
                @if($company->email)<div class="muted">{{ $company->email }}</div>@endif
            </td>
            <td class="col text-right">
                <div class="title">{{ match($preventa->tipo) { 'cotizacion' => 'Cotización', 'contrato' => 'Contrato', 'pedido' => 'Pedido', 'soporte' => 'Soporte técnico', default => $preventa->tipoLabel() } }}</div>
                <div><strong>{{ $preventa->numero }}</strong></div>
                <div>Fecha: {{ $preventa->fecha?->format('d/m/Y') }}</div>
                @if($preventa->fecha_vencimiento)
                    <div>Válido hasta: {{ $preventa->fecha_vencimiento->format('d/m/Y') }}</div>
                @endif
            </td>
        </tr></table>
    </div>

    <table class="row"><tr>
        <td class="col">
            <div class="box">
                <strong>Cliente</strong><br>
                {{ $preventa->cliente_nombre }}<br>
                @if($preventa->cliente_documento){{ $preventa->cliente_documento }}<br>@endif
                @if($preventa->cliente_email){{ $preventa->cliente_email }}<br>@endif
                @if($preventa->cliente_telefono){{ $preventa->cliente_telefono }}@endif
            </div>
        </td>
        <td class="col">
            <div class="box">
                <strong>Tipo de venta:</strong> {{ $preventa->documentTypeLabel() }}<br>
                <strong>Condición de pago:</strong> {{ $preventa->paymentConditionLabel() }}<br>
                <strong>Estado:</strong> {{ $preventa->estadoLabel() }}
            </div>
        </td>
    </tr></table>

    <div class="box">
        <strong>Asunto:</strong> {{ $preventa->titulo }}
        @if($preventa->descripcion)
            <br><br><strong>Detalle:</strong><br>{{ $preventa->descripcion }}
        @endif
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Producto / descripción</th>
                <th class="text-right" style="width:12%;">Cantidad</th>
                <th class="text-right" style="width:16%;">Precio unit.</th>
                <th class="text-right" style="width:16%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preventa->items as $item)
                <tr>
                    <td>{{ $item->descripcion }}</td>
                    <td class="text-right">{{ number_format((float) $item->cantidad, 4) }}</td>
                    <td class="text-right">S/ {{ number_format((float) $item->precio_unitario, 2) }}</td>
                    <td class="text-right">S/ {{ number_format((float) $item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">S/ {{ number_format((float) $preventa->subtotal, 2) }}</td></tr>
        <tr><td>IGV</td><td class="text-right">S/ {{ number_format((float) $preventa->igv, 2) }}</td></tr>
        <tr class="total"><td>Total</td><td class="text-right">S/ {{ number_format((float) $preventa->monto, 2) }}</td></tr>
    </table>

    @if($preventa->observaciones)
        <div class="box" style="margin-top: 16px;">
            <strong>Observaciones:</strong><br>{{ $preventa->observaciones }}
        </div>
    @endif

    @if($preventa->tipo === \App\Models\Preventa::TIPO_COTIZACION)
        <div class="signature">Aceptación del cliente</div>
        <div class="footer">
            Esta cotización está sujeta a disponibilidad de stock y confirmación del pedido.
        </div>
    @endif
</body>
</html>
