<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Compra</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', monospace; font-size: 10px; color: #000; padding: 5px; width: 76mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .sep { border-top: 1px dashed #000; margin: 4px 0; }
        .sep-double { border-top: 2px solid #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
    </style>
</head>
<body>
    <div class="text-center bold" style="font-size:11px;">COMPROBANTE DE COMPRA</div>
    <div class="sep"></div>
    <table>
        <tr><td>{{ $purchase->tipo_documento }} - {{ $purchase->numero_documento }}</td></tr>
        <tr><td>Fecha: {{ $purchase->fecha }}</td></tr>
        <tr><td>Proveedor: {{ $purchase->supplier->nombre ?? 'Sin proveedor' }}</td></tr>
    </table>
    <div class="sep"></div>
    <table>
        <tr><td><b>Cant</b></td><td><b>Producto</b></td><td class="text-right"><b>Importe</b></td></tr>
        @foreach($purchase->items as $item)
        <tr>
            <td valign="top">{{ $item->cantidad }}</td>
            <td valign="top">{{ \Illuminate\Support\Str::limit($item->product->descripcion ?? $item->descripcion ?? '—', 25) }}</td>
            <td class="text-right" valign="top">S/ {{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="sep-double"></div>
    <table>
        <tr class="bold" style="font-size:11px;">
            <td>TOTAL:</td>
            <td class="text-right">S/ {{ number_format($purchase->total, 2) }}</td>
        </tr>
    </table>
    <div class="sep"></div>
    <div class="text-center" style="font-size:8px;margin-top:5px;">
        FacturaFácil - {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
