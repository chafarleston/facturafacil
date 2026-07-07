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
                    <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Categoría</span>
                        <span class="info-box-number">{{ $product->category->nombre ?? 'Sin categoría' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon {{ $product->is_composite ? 'bg-secondary' : ($product->stock < 0 ? 'bg-danger' : ($product->stock == 0 ? 'bg-warning' : 'bg-info')) }}">
                        <i class="fas fa-cubes"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Stock</span>
                        <span class="info-box-number">
                            @if($product->is_composite)
                                <span class="text-muted">N/A (Compuesto)</span>
                            @else
                                {{ $product->stock }}
                                @if($product->stock < 0)
                                    <small class="text-muted">(Saldo negativo)</small>
                                @endif
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-dark"><i class="fas fa-utensils"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Destino KDS</span>
                        <span class="info-box-number">
                            @if(($product->kds_destination ?? 'cocina') == 'cocina2') KDS Cocina 2
                            @elseif(($product->kds_destination ?? 'cocina') == 'bar') KDS Bar
                            @else KDS Cocina
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if($product->is_composite)
        <hr>
        <h5><i class="fas fa-puzzle-piece text-warning"></i> Componentes del Producto Compuesto</h5>
        @if($product->components->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->components as $comp)
                    <tr>
                        <td>{{ $comp->component->descripcion ?? 'N/A' }}</td>
                        <td>{{ $comp->component->codigo ?? '-' }}</td>
                        <td>{{ $comp->quantity }}</td>
                        <td>S/ {{ number_format($comp->component->precio ?? 0, 2) }}</td>
                        <td>S/ {{ number_format(($comp->component->precio ?? 0) * $comp->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted">Sin componentes</p>
        @endif
        @endif
    </div>
    <div class="card-footer d-flex justify-content-between">
        <div>
            @if($prev)
            <a href="{{ route('products.show', $prev) }}" class="btn btn-outline-primary"><i class="fas fa-chevron-left"></i> Anterior</a>
            @endif
            @if($product->is_composite)
              <a href="{{ route('products.composite.edit', $product) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
            @else
              <a href="{{ route('products.edit', $product) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
            @endif
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Volver</a>
            @if($next)
            <a href="{{ route('products.show', $next) }}" class="btn btn-outline-primary">Siguiente <i class="fas fa-chevron-right"></i></a>
            @endif
        </div>
    </div>
</div>
@endsection