@extends('layouts.admin')
@section('title', 'Resumen de Caja')
@section('page_title', 'Resumen de Caja')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Resumen de Caja #{{ $cashregister->id }}</h3>
                <div class="card-tools float-right">
                    <a href="{{ route('cashregisters.pdf', $cashregister) }}" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF A4
                    </a>
                    <a href="{{ route('cashregisters.ticket', $cashregister) }}" class="btn btn-warning btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Ticket 80mm
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fecha Apertura</span>
                                <span class="info-box-number">{{ $cashregister->fecha_apertura->format('d/m/Y H:i') }}</span>
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
                <h4>S/ {{ number_format($cashregister->ventas_efectivo, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Tarjeta</h5>
                <h4>S/ {{ number_format($cashregister->ventas_tarjeta, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Yape</h5>
                <h4>S/ {{ number_format($cashregister->ventas_yape, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Plin</h5>
                <h4>S/ {{ number_format($cashregister->ventas_plin, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Otro</h5>
                <h4>S/ {{ number_format($cashregister->ventas_otro, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success">
            <div class="card-body text-center text-white">
                <h5>TOTAL</h5>
                <h4>S/ {{ number_format($cashregister->total_ventas, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('cashregisters.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection