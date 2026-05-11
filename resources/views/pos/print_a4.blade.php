<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->full_number }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            max-width: 210mm;
        }
        .invoice-container {
            border: 1px solid #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
        }
        .company-address {
            font-size: 10px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            border: 1px solid #000;
            padding: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 3px 5px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 80px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            border: 1px solid #000;
            background: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            text-align: center;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 6px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals {
            width: 250px;
            margin-left: auto;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .total-row.grand {
            font-weight: bold;
            font-size: 16px;
            border-top: 1px solid #000;
            padding-top: 8px;
            margin-top: 5px;
        }
        .amount-in-words {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
        }
        .qr-code {
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-name">{{ $invoice->company->nombre ?? 'EMPRESA' }}</div>
            <div class="company-address">
                {{ $invoice->company->direccion ?? '' }}<br>
                Tel: {{ $invoice->company->telefono ?? '' }}<br>
                RUC: {{ $invoice->company->ruc ?? '' }}
            </div>
        </div>
        
        <div class="document-title">
            {{ strtoupper($invoice->documentTypeName) }}<br>
            {{ $invoice->serie }} - {{ str_pad($invoice->numero, 8, '0', STR_PAD_LEFT) }}
        </div>
        
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Fecha:</div>
                <div class="info-cell">{{ \Carbon\Carbon::parse($invoice->fecha_emision)->format('d/m/Y') }}</div>
                <div class="info-cell info-label">Hora:</div>
                <div class="info-cell">{{ $invoice->hora_emision }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Señor(a):</div>
                <div class="info-cell" style="width: 250px;">{{ $invoice->customer->nombre ?? 'CLIENTE VARIOS' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">{{ $invoice->customer->documento_tipo ?? 'DNI/RUC' }}:</div>
                <div class="info-cell">{{ $invoice->customer->documento_numero ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Dirección:</div>
                <div class="info-cell">{{ $invoice->customer->direccion ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Moneda:</div>
                <div class="info-cell">SOLES</div>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Item</th>
                    <th>Codigo</th>
                    <th>Descripcion</th>
                    <th style="width: 50px;">Und.</th>
                    <th style="width: 70px;">Cant.</th>
                    <th style="width: 80px;">P.Unit</th>
                    <th style="width: 90px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->codigo ?? '' }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td class="text-center">NIU</td>
                    <td class="text-right">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($item->precio_venta, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals">
            <div class="total-row">
                <span>Op. Gravadas:</span>
                <span>S/ {{ number_format($invoice->gravado, 2) }}</span>
            </div>
            <div class="total-row">
                <span>IGV (18%):</span>
                <span>S/ {{ number_format($invoice->igv, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Exonerado:</span>
                <span>S/ {{ number_format($invoice->exonerado ?? 0, 2) }}</span>
            </div>
            <div class="total-row grand">
                <span>TOTAL:</span>
                <span>S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
        
        <div class="amount-in-words">
            SON: {{ $invoice->total_letras }}
        </div>
        
        <div class="qr-code">
            QR CODE<br>
            @if($invoice->sunat_estado === 'ACEPTADO')
            ACEPTADO POR SUNAT
            @else
            PENDIENTE DE ENVIO
            @endif
        </div>
        
        <div class="footer">
            Representación impresa de la {{ strtolower($invoice->documentTypeName) }} electrónica.<br>
            Consulte en: {{ config('app.url', 'sistemasfactura.com') }}
        </div>
    </div>
</body>
</html>