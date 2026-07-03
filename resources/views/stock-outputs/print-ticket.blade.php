<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Consumo</title>
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
    <div class="text-center bold" style="font-size:11px;">CONSUMO INTERNO #{{ $stockOutput->id }}</div>
    <div class="sep"></div>
    @php
        $m = [
            'consumo_cocina' => 'Consumo cocina',
            'merma' => 'Merma',
            'degustacion' => 'Degustación',
            'otro' => $stockOutput->motivo_otro ?? 'Otro',
        ];
    @endphp
    <table>
        <tr><td>Fecha: {{ $stockOutput->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Usuario: {{ $stockOutput->user->name ?? '-' }}</td></tr>
        <tr><td>Motivo: {{ $m[$stockOutput->motivo] ?? $stockOutput->motivo }}</td></tr>
        @if($stockOutput->referencia)
        <tr><td>Ref: {{ $stockOutput->referencia }}</td></tr>
        @endif
    </table>
    <div class="sep"></div>
    <table>
        <tr><td><b>Cant</b></td><td><b>Producto</b></td></tr>
        @foreach($stockOutput->items as $item)
        <tr>
            <td valign="top">{{ number_format($item->cantidad, 4) }}</td>
            <td valign="top">{{ \Illuminate\Support\Str::limit($item->product->descripcion ?? 'Producto #' . $item->product_id, 30) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="sep-double"></div>
    <div class="text-center" style="font-size:8px;margin-top:5px;">
        FacturaFácil - {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
