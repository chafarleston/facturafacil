@extends('layouts.admin')
@section('title', 'Resumen de Caja')
@section('page_title', 'Resumen de Caja')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Resumen de Caja #{{ $cashregister->id }}
                        @if($cashregister->referencia)
                            <span class="badge badge-info ml-2">{{ $cashregister->referencia }}</span>
                        @endif
                    </h3>
                <div class="card-tools float-right">
                    <a href="{{ route('cashregisters.pdf', $cashregister) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF A4
                    </a>
                    <a href="{{ route('cashregisters.ticket', $cashregister) }}" class="btn btn-warning btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Ticket 80mm
                    </a>
                    <form method="POST" action="{{ route('cashregisters.printCaja', $cashregister) }}" style="display:inline;" id="printCajaForm">
                        @csrf
                        <button type="button" class="btn btn-success btn-sm" onclick="showPrintConfirm()">
                            <i class="fas fa-receipt"></i> Imprimir en Caja
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fecha Apertura</span>
                                <span class="info-box-number">{{ $cashregister->fecha_apertura ? $cashregister->fecha_apertura->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-cash-register"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Monto Apertura</span>
                                <span class="info-box-number">S/ {{ number_format($cashregister->monto_apertura, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-cash-register"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Monto Cierre</span>
                                <span class="info-box-number">S/ {{ number_format($cashregister->monto_cierre ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Usuario</span>
                                <span class="info-box-number">{{ $cashregister->user->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<h4 class="mt-4">Resumen por Tipo de Documento</h4>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h5 class="card-title">Facturas</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-primary">{{ $facturas->count() }}</h2>
                <p>ventas</p>
                <h4 class="text-success">S/ {{ number_format($facturas->sum('total'), 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h5 class="card-title">Boletas</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-info">{{ $boletas->count() }}</h2>
                <p>ventas</p>
                <h4 class="text-success">S/ {{ number_format($boletas->sum('total'), 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
                <h5 class="card-title">Notas de Venta</h5>
            </div>
            <div class="card-body text-center">
                <h2 class="text-warning">{{ $nvs->count() }}</h2>
                <p>ventas</p>
                <h4 class="text-success">S/ {{ number_format($nvs->sum('total'), 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<h4 class="mt-4">Resumen por Método de Pago</h4>
<div class="row">
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Efectivo</h5>
                <h4>S/ {{ number_format($ventasEfectivo, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Tarjeta</h5>
                <h4>S/ {{ number_format($ventasTarjeta, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Yape</h5>
                <h4>S/ {{ number_format($ventasYape, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Plin</h5>
                <h4>S/ {{ number_format($ventasPlin, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Otro</h5>
                <h4>S/ {{ number_format($ventasOtro, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success">
            <div class="card-body text-center text-white">
                <h5>TOTAL</h5>
                <h4>S/ {{ number_format($totalMetodos, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

@if(count($categoriasVentas) > 0)
<h4 class="mt-4">Resumen por Categoría</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Categoría</th>
                <th class="text-right">Transacciones</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoriasVentas as $categoria => $data)
            <tr>
                <td>{{ $categoria }}</td>
                <td class="text-right">{{ $data['cantidad'] }}</td>
                <td class="text-right">S/ {{ number_format($data['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(count($productosVendidos) > 0)
<h4 class="mt-4">Productos Vendidos</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Producto</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productosVendidos as $producto => $data)
            <tr>
                <td>{{ $producto }}</td>
                <td class="text-right">{{ number_format($data['cantidad'], 2) }}</td>
                <td class="text-right">S/ {{ number_format($data['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<h4 class="mt-4">Lista de Comprobantes</h4>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Documento</th>
                <th>Cliente</th>
                <th class="text-right">Total</th>
                <th>Método Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
            <tr>
                <td>{{ $venta->full_number }}</td>
                <td>{{ $venta->customer->nombre ?? 'Cliente Varios' }}</td>
                <td class="text-right">S/ {{ number_format($venta->total, 2) }}</td>
                <td>{{ $venta->metodo_pago ?? 'Efectivo' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(count($lineasEliminadas) > 0)
<div class="mt-4">
    <h4>Reporte de Líneas Eliminadas</h4>
    <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Cant.</th>
                                <th>Producto</th>
                                <th>Eliminado por</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lineasEliminadas as $item)
                            <tr>
                                <td>x{{ number_format($item->quantity, 0) }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->cancelledBy->name ?? '-' }}</td>
                                <td>{{ $item->cancelled_at ? $item->cancelled_at->format('H:i') : '-' }}</td>
                            </tr>
                            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="mt-4">
    <a href="{{ route('cashregisters.index') }}" class="btn btn-secondary">Volver</a>
</div>

{{-- Confirm Modal --}}
<div class="confirm-overlay" id="printConfirmOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:white; padding:25px; border-radius:10px; min-width:350px; max-width:90%; text-align:center; box-shadow:0 5px 20px rgba(0,0,0,0.2);">
        <div style="font-size:40px; margin-bottom:10px;"><i class="fas fa-print" style="color:#28a745;"></i></div>
        <h5 style="margin:0 0 10px 0;">Imprimir resumen</h5>
        <p style="color:#666; margin-bottom:20px;">¿Enviar resumen de caja a la impresora Caja?</p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button type="button" class="btn btn-secondary" onclick="closePrintConfirm()">Cancelar</button>
            <button type="button" class="btn btn-success" onclick="submitPrintCaja()">Imprimir</button>
        </div>
    </div>
</div>

<style>
.confirm-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center; }
</style>
<script>
function showPrintConfirm() { document.getElementById('printConfirmOverlay').style.display = 'flex'; }
function closePrintConfirm() { document.getElementById('printConfirmOverlay').style.display = 'none'; }
function submitPrintCaja() { closePrintConfirm(); document.getElementById('printCajaForm').submit(); }
</script>
@endsection