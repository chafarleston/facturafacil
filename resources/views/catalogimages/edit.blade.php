@extends('layouts.admin')
@section('title', 'Imágenes de Venta')
@section('page_title', 'Imágenes de Venta')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Imágenes en Módulos de Venta</h3>
    </div>
    <form method="POST" action="{{ route('catalog-images.update') }}">
        @csrf
        <div class="card-body">
            <p class="text-muted mb-4">
                Active las imágenes solo en los módulos que las necesiten. Desactivadas (por defecto) el sistema
                funciona igual que antes, sin cargar imágenes y de forma más rápida.
            </p>
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-box mr-1"></i> Imágenes de Productos</h3>
                        </div>
                        <div class="card-body">
                            @foreach($modules as $key => $label)
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" name="img_products_{{ $key }}" value="1"
                                       class="custom-control-input" id="prod_{{ $key }}"
                                       {{ ($productImages[$key] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="prod_{{ $key }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Imágenes de Categorías</h3>
                        </div>
                        <div class="card-body">
                            @foreach($modules as $key => $label)
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" name="img_categories_{{ $key }}" value="1"
                                       class="custom-control-input" id="cat_{{ $key }}"
                                       {{ ($categoryImages[$key] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="cat_{{ $key }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-layer-group mr-1"></i> Visualización</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <strong>Mostrar solo categorías:</strong> al abrir el módulo se muestran solo las
                                categorías y los productos aparecen al hacer clic en una categoría.
                            </p>
                            @foreach($modules as $key => $label)
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" name="catonly_{{ $key }}" value="1"
                                       class="custom-control-input" id="catonly_{{ $key }}"
                                       {{ ($categoriesOnly[$key] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="catonly_{{ $key }}">{{ $label }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </div>
    </form>
</div>
@endsection
