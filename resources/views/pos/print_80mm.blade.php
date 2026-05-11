<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->full_number }}</title>
    <style>
        @page {
            margin: 5mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 76mm;
            margin: 0 auto;
            padding: 5px;
            background: #fff;
        }
        .ticket {
            width: 100%;
            text-align: center;
        }
        .header-text {
            font-weight: bold;
            font-size: 14px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .separator-double {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin: 5px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .info-label {
            font-weight: bold;
        }
        .info-value {
            text-align: right;
        }
        .items-list {
            text-align: left;
            width: 100%;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .item-desc {
            flex: 1;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .item-qty {
            width: 30px;
            text-align: center;
        }
        .item-price {
            width: 55px;
            text-align: right;
        }
        .item-line {
            border-bottom: 1px dashed #000;
            margin: 2px 0;
        }
        .totals-section {
            margin-top: 5px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .amount-words {
            text-align: center;
            border: 1px solid #000;
            padding: 5px;
            margin: 8px 0;
            font-size: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
        }
        .sunat-status {
            font-size: 10px;
            text-align: center;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="company-name">{{ $invoice->company->nombre ?? 'EMPRESA' }}</div>
        <div>{{ $invoice->company->direccion ?? '' }}</div>
        <div>RUC: {{ $invoice->company->ruc ?? '' }}</div>
        
        <div class="separator-double"></div>
        
        <div class="header-text">{{ strtoupper($invoice->documentTypeName) }}</div>
        <div>{{ $invoice->serie }}-{{ str_pad($invoice->numero, 8, '0', STR_PAD_LEFT) }}</div>
        
        <div class="separator"></div>
        
        <div class="info-row">
            <span class="info-label">Fecha:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($invoice->fecha_emision)->format('d/m/Y') }} {{ $invoice->hora_emision }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value">{{ $invoice->customer->nombre ?? 'VARIOS' }}</span>
        </div>
        @if($invoice->customer && $invoice->customer->documento_numero)
        <div class="info-row">
            <span class="info-label">{{ $invoice->customer->documento_tipo ?? 'DNI' }}:</span>
            <span class="info-value">{{ $invoice->customer->documento_numero }}</span>
        </div>
        @endif
        
        <div class="separator-double"></div>
        
        <div class="items-list">
            @foreach($invoice->items as $item)
            <div class="item-desc">{{ $item->descripcion }}</div>
            <div class="item-row">
                <span class="item-qty">x{{ number_format($item->cantidad, 0) }}</span>
                <span class="item-price">S/ {{ number_format($item->precio_venta, 2) }}</span>
            </div>
            <div class="item-line"></div>
            @endforeach
        </div>
        
        <div class="totals-section">
            <div class="total-row">
                <span>GRAVADA:</span>
                <span>S/ {{ number_format($invoice->gravado, 2) }}</span>
            </div>
            <div class="total-row">
                <span>IGV 18%:</span>
                <span>S/ {{ number_format($invoice->igv, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
        
        <div class="amount-words">
            SON: {{ $invoice->total_letras }}
        </div>
        
        <div class="sunat-status">
            @if($invoice->sunat_estado === 'ACEPTADO')
            *** ACEPTADO SUNAT ***
            @else
            *** PENDIENTE ***
            @endif
        </div>
        
        <div class="separator"></div>
        
        <div class="footer">
            Gracias por su compra<br>
            Consulte su comprobante en<br>
            {{ config('app.url', 'sistemasfactura.com') }}
        </div>
    </div>
</body>
</html>