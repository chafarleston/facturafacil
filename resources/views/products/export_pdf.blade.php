<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #222;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 17px;
            color: #1f2937;
        }
        .header p {
            margin: 3px 0;
            color: #555;
        }
        .category-title {
            margin-top: 12px;
            padding: 6px 8px;
            background: #e9ecef;
            border-left: 4px solid #007bff;
            font-size: 11px;
            font-weight: bold;
            color: #1f2937;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        th {
            background: #007bff;
            color: #fff;
            padding: 5px 4px;
            border: 1px solid #0056b3;
            text-align: left;
            font-size: 8px;
        }
        td {
            padding: 4px;
            border: 1px solid #d1d5db;
        }
        tr:nth-child(even) td {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            color: #888;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->nombre_comercial ?? $company->razon_social ?? 'Lista de Productos' }}</h1>
        <p>
            @if($company && $company->ruc)
                RUC: {{ $company->ruc }} |
            @endif
            Productos: {{ $products->count() }} |
            Generado: {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>

    @foreach($groups as $categoryName => $categoryProducts)
        <div class="category-title">{{ $categoryName }} ({{ $categoryProducts->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 9%;">Cód. Barras</th>
                    <th style="width: 29%;">Descripción</th>
                    <th style="width: 8%;">U.Medida</th>
                    <th style="width: 9%;" class="text-right">Precio</th>
                    <th style="width: 8%;" class="text-right">Stock</th>
                    <th style="width: 8%;" class="text-right">IGV %</th>
                    <th style="width: 9%;">Tipo</th>
                    <th style="width: 10%;">Destino KDS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryProducts as $product)
                    <tr>
                        <td>{{ $product->codigo }}</td>
                        <td>{{ $product->codigo_barras ?? '-' }}</td>
                        <td>{{ $product->descripcion }}</td>
                        <td>{{ $product->umedida_codigo ?? 'NIU' }}</td>
                        <td class="text-right">S/ {{ number_format((float) $product->precio, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $product->stock, 2) }}</td>
                        <td class="text-right">{{ number_format((float) $product->igv_percent, 2) }}</td>
                        <td>{{ $product->tipo_afectacion ?? 'GRA' }}</td>
                        <td>{{ $product->kds_destination ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">Sistema FacturaFácil - Lista de productos generada automáticamente</div>
</body>
</html>
