@extends('layouts.admin')
@section('title', 'Configuración Kiosko')
@section('page_title', 'Configuración Kiosko')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-desktop"></i> Configuración Kiosko</h3>
            </div>
            <form method="POST" action="{{ route('kiosk-settings.update') }}">
                @csrf
                <div class="card-body">
                    @if(!$kioskTable)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No existe una mesa kiosko. Se creará automáticamente al guardar (requiere al menos un piso en Restaurante → Pisos).
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="kiosk_table_name">Nombre de la mesa kiosko</label>
                        <input type="text" name="kiosk_table_name" id="kiosk_table_name" class="form-control"
                            maxlength="100" value="{{ old('kiosk_table_name', $kioskTable->name ?? 'Kiosko') }}" required>
                        <small class="text-muted">Nombre interno con el que se registran los pedidos del autoservicio.</small>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-images"></i> Visualización del catálogo</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="img_products_kiosko" id="img_products_kiosko" value="1"
                            {{ $showProductImages ? 'checked' : '' }}>
                        <label class="form-check-label" for="img_products_kiosko">Mostrar imágenes de productos</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="img_categories_kiosko" id="img_categories_kiosko" value="1"
                            {{ $showCategoryImages ? 'checked' : '' }}>
                        <label class="form-check-label" for="img_categories_kiosko">Mostrar imágenes de categorías</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="catonly_kiosko" id="catonly_kiosko" value="1"
                            {{ $categoriesOnly ? 'checked' : '' }}>
                        <label class="form-check-label" for="catonly_kiosko">Mostrar solo categorías al inicio</label>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                        <a href="{{ route('restaurant.kioskOrders') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                    <a href="{{ route('autopedido.index') }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Abrir AutoPedido
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
