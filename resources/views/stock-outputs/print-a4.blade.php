<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consumo Interno #{{ $stockOutput->id }}</title>
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
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CONSUMO INTERNO</h2>
        @if($stockOutput->company)
        <p>{{ $stockOutput->company->nombre ?? $stockOutput->company->razon_social ?? '' }}</p>
        @endif
    </div>

    <div class="info">
        @php
            $m = [
                'consumo_cocina' => 'Consumo cocina',
                'merma' => 'Merma',
                'degustacion' => 'Degustación',
                'otro' => $stockOutput->motivo_otro ?? 'Otro',
            ];
        @endphp
        <table>
            <tr><td class="label">N°:</td><td>{{ $stockOutput->id }}</td></tr>
            <tr><td class="label">Fecha:</td><td>{{ $stockOutput->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td class="label">Usuario:</td><td>{{ $stockOutput->user->name ?? '-' }}</td></tr>
            <tr><td class="label">Motivo:</td><td>{{ $m[$stockOutput->motivo] ?? $stockOutput->motivo }}</td></tr>
            @if($stockOutput->referencia)
            <tr><td class="label">Referencia:</td><td>{{ $stockOutput->referencia }}</td></tr>
            @endif
            @if($stockOutput->notas)
            <tr><td class="label">Notas:</td><td>{{ $stockOutput->notas }}</td></tr>
            @endif
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:50%">Producto</th>
                <th style="width:15%" class="text-right">Cantidad</th>
                <th style="width:15%" class="text-right">Stock Antes</th>
                <th style="width:20%" class="text-right">Stock Después</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockOutput->items as $item)
            <tr>
                <td>{{ $item->product->descripcion ?? 'Producto #' . $item->product_id }}</td>
                <td class="text-right">{{ number_format($item->cantidad, 4) }}</td>
                <td class="text-right">{{ number_format($item->stock_antes, 4) }}</td>
                <td class="text-right">{{ number_format($item->stock_despues, 4) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento generado por FacturaFácil - {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
