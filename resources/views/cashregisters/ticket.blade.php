<?php use App\Models\Company; $company = Company::getMainCompany(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resumen de Caja</title>
    <style>
        @media print { body { width: 80mm; } }
        body { font-family: "Courier New", monospace; font-size: 9px; padding: 8px; width: 76mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .border-top { border-bottom: 1px solid #000; }
        .border-double { border-bottom: 2px solid #000; }
    </style>
</head>
<body>
    <div class="text-center py-1">
        <div class="bold">{{ $company->nombre_comercial ?? $company->razon_social }}</div>
        <div>RUC: {{ $company->ruc }}</div>
        <div class="bold" style="font-size:11px;">RESUMEN DE CAJA</div>
    </div>

    <div class="border-bottom py-1 mb-1">
        <div>Apertura: {{ $cashregister->fecha_apertura->format('d/m/Y H:i') }}</div>
        <div>Cierre: {{ $cashregister->fecha_cierre ? $cashregister->fecha_cierre->format('d/m/Y H:i') : 'Ahora' }}</div>
        <div>{{ $cashregister->user->name }}</div>
    </div>

    <div class="border-top py-1 mb-1 bold">RESUMEN POR DOCUMENTO</div>
    <div>
        <div class="bold">Facturas:</div>
        <div>{{ $facturas->count() }} und - S/ {{ number_format($facturas->sum('total'), 2) }}</div>
    </div>
    <div>
        <div class="bold">Boletas:</div>
        <div>{{ $boletas->count() }} und - S/ {{ number_format($boletas->sum('total'), 2) }}</div>
    </div>
    <div>
        <div class="bold">Notas Venta:</div>
        <div>{{ $nvs->count() }} und - S/ {{ number_format($nvs->sum('total'), 2) }}</div>
    </div>
    <div class="border-top py-1 mt-1">
        <div class="bold">TOTAL: {{ $facturas->count() + $boletas->count() + $nvs->count() }} und</div>
        <div class="bold">S/ {{ number_format($cashregister->total_ventas, 2) }}</div>
    </div>

    <div class="border-top py-1 mt-1 mb-1 bold">POR MÉTODO PAGO</div>
    @php
        $calcEfectivo = 0; $calcTarjeta = 0; $calcYape = 0; $calcPlin = 0; $calcOtro = 0;
        foreach ($ventas as $v) {
            $metodo = $v->metodo_pago ?? 'EFECTIVO';
            if (str_contains($metodo, ' + ')) {
                foreach (explode(' + ', $metodo) as $part) {
                    $part = trim($part);
                    $met = str_contains($part, '/') ? explode('/', $part)[0] : $part;
                    $amt = str_contains($part, '/') ? (float) explode('/', $part)[1] : (float) $v->total / count(explode(' + ', $metodo));
                    match ($met) { 'EFECTIVO' => $calcEfectivo += $amt, 'TARJETA' => $calcTarjeta += $amt, 'YAPE' => $calcYape += $amt, 'PLIN' => $calcPlin += $amt, default => $calcOtro += $amt, };
                }
            } elseif (str_contains($metodo, '/')) {
                [$met, $amt] = explode('/', $metodo);
                $amt = (float) $amt;
                match ($met) { 'EFECTIVO' => $calcEfectivo += $amt, 'TARJETA' => $calcTarjeta += $amt, 'YAPE' => $calcYape += $amt, 'PLIN' => $calcPlin += $amt, default => $calcOtro += $amt, };
            } else {
                match ($metodo) { 'EFECTIVO' => $calcEfectivo += (float) $v->total, 'TARJETA' => $calcTarjeta += (float) $v->total, 'YAPE' => $calcYape += (float) $v->total, 'PLIN' => $calcPlin += (float) $v->total, default => $calcOtro += (float) $v->total, };
            }
        }
    @endphp
    <div>
        <div>Efectivo: S/ {{ number_format($calcEfectivo, 2) }}</div>
        <div>Tarjeta: S/ {{ number_format($calcTarjeta, 2) }}</div>
        <div>Yape: S/ {{ number_format($calcYape, 2) }}</div>
        <div>Plin: S/ {{ number_format($calcPlin, 2) }}</div>
        <div>Otro: S/ {{ number_format($calcOtro, 2) }}</div>
    </div>

    <div class="border-top py-1 mt-1 mb-1 bold">LISTA DE COMPROBANTES</div>
    <div style="font-size:7px; border-bottom:1px dashed #000; padding-bottom:2px; margin-bottom:2px; display:flex;">
        <span style="flex:1;">Documento</span>
        <span style="flex:1;">Cliente</span>
        <span style="text-align:right;">Total</span>
    </div>
    @foreach($ventas as $venta)
    <div style="font-size:7px; display:flex; margin-bottom:1px;">
        <span style="flex:1;">{{ $venta->full_number }}</span>
        <span style="flex:1;">{{ $venta->customer->nombre ?? 'Varios' }}</span>
        <span style="text-align:right;">S/ {{ number_format($venta->total, 2) }}</span>
    </div>
    <div style="font-size:6px; display:flex;">
        <span style="flex:1; color:#888;">Pago: {{ $venta->metodo_pago ?? 'EFECTIVO' }}</span>
    </div>
    @endforeach

    @if(count($categoriasVentas) > 0)
    <div class="border-top py-1 mt-1 mb-1 bold">POR CATEGORÍA</div>
    <div style="font-size:8px; border-bottom:1px dashed #000; padding-bottom:2px; margin-bottom:2px; display:flex;">
        <span style="min-width:20px;">Cant.</span>
        <span style="flex:1; padding:0 4px;">Categoría</span>
        <span style="text-align:right;">Precio</span>
    </div>
    @foreach($categoriasVentas as $categoria => $data)
    <div style="font-size:8px; display:flex;">
        <span style="min-width:20px;">{{ $data['cantidad'] }}</span>
        <span style="flex:1; padding:0 4px;">{{ $categoria }}</span>
        <span style="text-align:right;">S/ {{ number_format($data['total'], 2) }}</span>
    </div>
    @endforeach
    @endif

    @if(count($productosVendidos) > 0)
    <div class="border-top py-1 mt-1 mb-1 bold">PRODUCTOS VENDIDOS</div>
    <div style="font-size:8px; border-bottom:1px dashed #000; padding-bottom:2px; margin-bottom:2px; display:flex;">
        <span style="min-width:20px;">Cant.</span>
        <span style="flex:1; padding:0 4px;">Producto</span>
        <span style="text-align:right;">Precio</span>
    </div>
    @foreach($productosVendidos as $producto => $data)
    <div style="font-size:8px; display:flex;">
        <span style="min-width:20px;">{{ $data['cantidad'] }}</span>
        <span style="flex:1; padding:0 4px;">{{ $producto }}</span>
        <span style="text-align:right;">S/ {{ number_format($data['total'], 2) }}</span>
    </div>
    @endforeach
    @endif

    @if(count($lineasEliminadas) > 0)
    <div class="border-top py-1 mt-1"></div>
    <div class="border-bottom py-1 mb-1 bold text-center">REPORTE DE LÍNEAS ELIMINADAS</div>
    <div style="font-size:8px; margin-bottom:3px;">Hay {{ count($lineasEliminadas) }} línea(s) eliminada(s) en el Sistema</div>
    @foreach($lineasEliminadas as $item)
        <div style="font-size:7px; margin-bottom:2px;">
            <span>x{{ number_format($item->quantity, 0) }} - {{ Str::limit($item->product_name, 16) }} - {{ $item->cancelledBy->name ?? '' }}</span>
            <span>{{ $item->cancelled_at ? $item->cancelled_at->format('H:i') : '' }}</span>
        </div>
    @endforeach
    <div class="border-top py-1 mt-1"></div>
    @endif

    <div class="border-top py-1 mt-1 text-center">
        <div class="bold">GRACIAS POR SU PREFERENCIA</div>
    </div>
</body>
</html>