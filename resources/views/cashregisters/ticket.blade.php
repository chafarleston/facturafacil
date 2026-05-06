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
        <div>Fecha: {{ $cashregister->fecha_apertura->format('d/m/Y') }}</div>
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
    <div>
        <div>Efectivo: S/ {{ number_format($cashregister->ventas_efectivo, 2) }}</div>
        <div>Tarjeta: S/ {{ number_format($cashregister->ventas_tarjeta, 2) }}</div>
        <div>Yape: S/ {{ number_format($cashregister->ventas_yape, 2) }}</div>
        <div>Plin: S/ {{ number_format($cashregister->ventas_plin, 2) }}</div>
    </div>

    <div class="border-top py-1 mt-1 text-center">
        <div class="bold">GRACIAS POR SU PREFERENCIA</div>
    </div>
</body>
</html>