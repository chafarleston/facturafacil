<?php use App\Models\Company; $company = Company::getMainCompany(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resumen de Caja</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .border-bottom { border-bottom: 1px solid #ddd; }
        .border-top { border-bottom: 1px solid #000; }
        .border-double { border-bottom: 2px solid #000; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="bold" style="font-size:14px;">{{ $company->nombre_comercial ?? $company->razon_social }}</div>
        <div>RUC: {{ $company->ruc }}</div>
        <div class="bold" style="font-size:16px; margin-top:10px;">RESUMEN DE CAJA</div>
        <div>Caja #{{ $cashregister->id }}</div>
    </div>

    <table>
        <tr>
            <td>Fecha:</td>
            <td class="text-right">{{ $cashregister->fecha_apertura->format('d/m/Y H:i') }} - {{ $cashregister->fecha_cierre ? $cashregister->fecha_cierre->format('d/m/Y H:i') : 'Ahora' }}</td>
        </tr>
        <tr>
            <td>Usuario:</td>
            <td class="text-right">{{ $cashregister->user->name }}</td>
        </tr>
        <tr>
            <td>Monto Apertura:</td>
            <td class="text-right">S/ {{ number_format($cashregister->monto_apertura, 2) }}</td>
        </tr>
        <tr>
            <td>Monto Cierre:</td>
            <td class="text-right">S/ {{ number_format($cashregister->monto_cierre ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="border-top py-2 mt-2 mb-1 bold">RESUMEN POR TIPO DE DOCUMENTO</div>
    <table>
        <tr class="bold border-bottom">
            <td>Tipo</td>
            <td class="text-right">Cantidad</td>
            <td class="text-right">Total</td>
        </tr>
        <tr>
            <td>Facturas (01):</td>
            <td class="text-right">{{ $facturas->count() }}</td>
            <td class="text-right">S/ {{ number_format($facturas->sum('total'), 2) }}</td>
        </tr>
        <tr>
            <td>Boletas (03):</td>
            <td class="text-right">{{ $boletas->count() }}</td>
            <td class="text-right">S/ {{ number_format($boletas->sum('total'), 2) }}</td>
        </tr>
        <tr>
            <td>Notas de Venta (NV):</td>
            <td class="text-right">{{ $nvs->count() }}</td>
            <td class="text-right">S/ {{ number_format($nvs->sum('total'), 2) }}</td>
        </tr>
        <tr class="bold border-top">
            <td>TOTAL:</td>
            <td class="text-right">{{ $facturas->count() + $boletas->count() + $nvs->count() }}</td>
            <td class="text-right">S/ {{ number_format($cashregister->total_ventas, 2) }}</td>
        </tr>
    </table>

    <div class="border-top py-2 mt-2 mb-1 bold">RESUMEN POR MÉTODO DE PAGO</div>
    <table>
        <tr>
            <td>Efectivo:</td>
            <td class="text-right">S/ {{ number_format($cashregister->ventas_efectivo, 2) }}</td>
        </tr>
        <tr>
            <td>Tarjeta:</td>
            <td class="text-right">S/ {{ number_format($cashregister->ventas_tarjeta, 2) }}</td>
        </tr>
        <tr>
            <td>Yape:</td>
            <td class="text-right">S/ {{ number_format($cashregister->ventas_yape, 2) }}</td>
        </tr>
        <tr>
            <td>Plin:</td>
            <td class="text-right">S/ {{ number_format($cashregister->ventas_plin, 2) }}</td>
        </tr>
        <tr>
            <td>Otro:</td>
            <td class="text-right">S/ {{ number_format($cashregister->ventas_otro, 2) }}</td>
        </tr>
        <tr class="bold border-top">
            <td>TOTAL VENTAS:</td>
            <td class="text-right">S/ {{ number_format($cashregister->total_ventas, 2) }}</td>
        </tr>
    </table>

    <div class="text-center mt-4" style="font-size:9px;">
        Documento generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>