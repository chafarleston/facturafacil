@extends('layouts.admin')
@section('title', 'Ver Producto')
@section('page_title', 'Ver Producto')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Producto: {{ $product->descripcion }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-box"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Código Interno</span>
                        <span class="info-box-number">{{ $product->codigo }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-barcode"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Código SUNAT</span>
                        <span class="info-box-number">{{ $product->codigo_sunat ?: 'No asignado' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Precio</span>
                        <span class="info-box-number">S/ {{ number_format($product->precio, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-percent"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tipo Afectación</span>
                        <span class="info-box-number">{{ $product->tipo_afectacion }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-ruler"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unidad de Medida</span>
                        <span class="info-box-number">{{ $product->umedida_codigo }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ $product->estado == 'ACT' ? 'success' : 'danger' }}"><i class="fas fa-power-off"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number">{{ $product->estado }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon {{ $product->stock < 0 ? 'bg-danger' : ($product->stock == 0 ? 'bg-warning' : 'bg-info') }}">
                        <i class="fas fa-cubes"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Stock</span>
                        <span class="info-box-number {{ $product->stock < 0 ? 'text-danger font-weight-bold' : '' }}">
                            {{ $product->stock }}
                            @if($product->stock < 0)
                                <small class="text-muted">(Saldo negativo)</small>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection