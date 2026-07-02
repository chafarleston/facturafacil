<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Compra {{ $purchase->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; }
        .info { margin-bottom: 15px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 3px 5px; }
        .info .label { font-weight: bold; width: 120px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #333; color: #fff; padding: 8px 5px; text-align: left; }
        table.items td { padding: 6px 5px; border-bottom: 1px solid #ddd; }
        table.items .text-right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 13px; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>COMPROBANTE DE COMPRA</h2>
    </div>

    <div class="info">
        <table>
            <tr><td class="label">Documento:</td><td>{{ $purchase->tipo_documento }} - {{ $purchase->numero_documento }}</td></tr>
            <tr><td class="label">Fecha:</td><td>{{ $purchase->fecha }}</td></tr>
            <tr><td class="label">Proveedor:</td><td>{{ $purchase->supplier->nombre ?? 'Sin proveedor' }}</td></tr>
            @if($purchase->supplier && $purchase->supplier->ruc)
            <tr><td class="label">RUC:</td><td>{{ $purchase->supplier->ruc }}</td></tr>
            @endif
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:50%">Producto</th>
                <th style="width:15%" class="text-right">Cantidad</th>
                <th style="width:15%" class="text-right">P. Unitario</th>
                <th style="width:20%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product->descripcion ?? $item->descripcion ?? '—' }}</td>
                <td class="text-right">{{ $item->cantidad }}</td>
                <td class="text-right">S/ {{ number_format($item->precio_unitario, 2) }}</td>
                <td class="text-right">S/ {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">S/ {{ number_format($purchase->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Documento generado por FacturaFácil - {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
