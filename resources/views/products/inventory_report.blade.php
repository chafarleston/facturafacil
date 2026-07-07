@extends('layouts.admin')
@section('title', 'Reporte de Inventario')
@section('page_title', 'Reporte de Inventario')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Inventario de Productos</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('products.inventory.report') }}" class="form-inline">
                        <input type="hidden" name="company_id" value="{{ $companyId ?? null }}">
                        <select name="category_id" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ ($categoryId ?? '') == $category->id ? 'selected' : '' }}>{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('products.inventory.report.excel', ['company_id' => $companyId, 'category_id' => $categoryId]) }}" class="btn btn-success btn-sm ml-2">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                    <a href="{{ route('products.inventory.report.pdf', ['company_id' => $companyId, 'category_id' => $categoryId]) }}" class="btn btn-danger btn-sm ml-1" target="_blank">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Productos</span>
                                <span class="info-box-number">{{ $totalProductos }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-cubes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Stock</span>
                                <span class="info-box-number">{{ number_format($totalStock, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Total (Venta)</span>
                                <span class="info-box-number">S/ {{ number_format($totalValorVenta, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-shopping-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Total (Costo)</span>
                                <span class="info-box-number">S/ {{ number_format($totalValorCosto, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th class="text-right">Stock</th>
                            <th class="text-right">Precio Compra</th>
                            <th class="text-right">Precio Venta</th>
                            <th class="text-right">Valor Total (Venta)</th>
                            <th class="text-right">Valor Total (Costo)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->codigo }}</td>
                            <td>{{ $product->descripcion }}</td>
                            <td>{{ $product->category->nombre ?? 'Sin categoría' }}</td>
                            <td class="text-right">
                                @if($product->stock < 0)
                                    <span class="text-danger font-weight-bold">{{ number_format($product->stock, 2) }}</span>
                                @elseif($product->stock == 0)
                                    <span class="text-warning font-weight-bold">0.00</span>
                                @else
                                    {{ number_format($product->stock, 2) }}
                                @endif
                            </td>
                            <td class="text-right">S/ {{ number_format($product->precio_compra, 2) }}</td>
                            <td class="text-right">S/ {{ number_format($product->precio, 2) }}</td>
                            <td class="text-right">S/ {{ number_format($product->stock * $product->precio, 2) }}</td>
                            <td class="text-right">S/ {{ number_format($product->stock * $product->precio_compra, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">No hay productos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
