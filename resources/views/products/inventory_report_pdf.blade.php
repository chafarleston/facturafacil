<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .summary-row {
            display: inline-block;
            width: 24%;
            text-align: center;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
            font-size: 9px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #0056b3;
        }
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-warning {
            color: #ffc107;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inventario</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-label">Total Productos</div>
            <div class="summary-value">{{ $totalProductos }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Total Stock</div>
            <div class="summary-value">{{ number_format($totalStock, 2) }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Valor Venta</div>
            <div class="summary-value">S/ {{ number_format($totalValorVenta, 2) }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Valor Costo</div>
            <div class="summary-value">S/ {{ number_format($totalValorCosto, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th class="text-right">Stock</th>
                <th class="text-right">P. Compra</th>
                <th class="text-right">P. Venta</th>
                <th class="text-right">Valor Venta</th>
                <th class="text-right">Valor Costo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->codigo }}</td>
                <td>{{ $product->descripcion }}</td>
                <td>{{ $product->category->nombre ?? 'Sin categoría' }}</td>
                <td class="text-right">
                    @if($product->stock < 0)
                        <span class="text-danger">{{ number_format($product->stock, 2) }}</span>
                    @elseif($product->stock == 0)
                        <span class="text-warning">0.00</span>
                    @else
                        {{ number_format($product->stock, 2) }}
                    @endif
                </td>
                <td class="text-right">S/ {{ number_format($product->precio_compra, 2) }}</td>
                <td class="text-right">S/ {{ number_format($product->precio, 2) }}</td>
                <td class="text-right">S/ {{ number_format($product->stock * $product->precio, 2) }}</td>
                <td class="text-right">S/ {{ number_format($product->stock * $product->precio_compra, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema FacturaFácil - Reporte generado automáticamente
    </div>
</body>
</html>
