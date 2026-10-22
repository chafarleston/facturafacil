@extends('layouts.admin')
@section('title', 'Configuración Restaurante')
@section('page_title', 'Configuración Restaurante')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-utensils"></i> Configuración Restaurante</h3>
            </div>
            <form method="POST" action="{{ route('restaurant-settings.update') }}">
                @csrf
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-images"></i> Visualización del catálogo</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="img_products_restaurant" id="img_products_restaurant" value="1"
                            {{ $showProductImages ? 'checked' : '' }}>
                        <label class="form-check-label" for="img_products_restaurant">Mostrar imágenes de productos</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="img_categories_restaurant" id="img_categories_restaurant" value="1"
                            {{ $showCategoryImages ? 'checked' : '' }}>
                        <label class="form-check-label" for="img_categories_restaurant">Mostrar imágenes de categorías</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="catonly_restaurant" id="catonly_restaurant" value="1"
                            {{ $categoriesOnly ? 'checked' : '' }}>
                        <label class="form-check-label" for="catonly_restaurant">Mostrar solo categorías al inicio</label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                    <a href="{{ route('restaurant.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Modo de Pedidos</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Modo actual:
                    <span class="badge badge-{{ $orderMode === 'print' ? 'info' : 'secondary' }}">
                        <i class="fas {{ $orderMode === 'print' ? 'fa-print' : 'fa-tv' }}"></i>
                        {{ $orderMode === 'print' ? 'Impresión 80mm' : 'KDS' }}
                    </span>
                </p>
                <p class="text-muted mb-3">
                    El modo de pedidos se cambia desde la opción <b>Modo de Pedidos</b>.
                </p>
                @can('permission', 'view_order_mode')
                <a href="{{ route('restaurant.mode') }}" class="btn btn-primary">
                    <i class="fas fa-exchange-alt"></i> Cambiar modo
                </a>
                @endcan
                <a href="{{ route('restaurant.index') }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> Abrir POS Restaurante
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
