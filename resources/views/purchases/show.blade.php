@extends('layouts.admin')
@section('title', 'Ver Compra')
@section('page_title', 'Ver Compra')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $purchase->tipo_documento }} - {{ $purchase->numero_documento }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Fecha</span>
                        <span class="info-box-number">{{ $purchase->fecha }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Proveedor</span>
                        <span class="info-box-number">{{ $purchase->supplier->nombre ?? 'Sin proveedor' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number">S/ {{ number_format($purchase->total, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ $purchase->estado == 'REGISTRADO' ? 'success' : 'danger' }}"><i class="fas fa-power-off"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number">{{ $purchase->estado }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <h4 class="mt-4">Productos</h4>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->descripcion }}</td>
                    <td class="text-right">{{ $item->cantidad }}</td>
                    <td class="text-right">S/ {{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ route('purchases.index', ['company_id' => $purchase->company_id]) }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection