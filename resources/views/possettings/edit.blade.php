@extends('layouts.admin')
@section('title', 'Configuración de Punto de Venta')
@section('page_title', 'Configuración de Punto de Venta')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cash-register"></i> Configuración de Punto de Venta</h3>
            </div>
            <form method="POST" action="{{ route('pos-settings.update') }}">
                @csrf
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-images"></i> Visualización del catálogo</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="img_products_pos" id="img_products_pos" value="1"
                            {{ $showProductImages ? 'checked' : '' }}>
                        <label class="form-check-label" for="img_products_pos">Mostrar imágenes de productos</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="img_categories_pos" id="img_categories_pos" value="1"
                            {{ $showCategoryImages ? 'checked' : '' }}>
                        <label class="form-check-label" for="img_categories_pos">Mostrar imágenes de categorías</label>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-file-invoice"></i> Ventas</h5>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tipo de comprobante por defecto</label>
                            <select name="default_document_type" class="form-control">
                                <option value="03" {{ $defaultDocumentType === '03' ? 'selected' : '' }}>Boleta</option>
                                <option value="01" {{ $defaultDocumentType === '01' ? 'selected' : '' }}>Factura</option>
                                <option value="NV" {{ $defaultDocumentType === 'NV' ? 'selected' : '' }}>Nota de Venta</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Método de pago por defecto</label>
                            <select name="default_payment_method" class="form-control">
                                @foreach(['EFECTIVO' => 'Efectivo', 'TARJETA' => 'Tarjeta', 'TRANSFERENCIA' => 'Transferencia', 'YAPE' => 'Yape', 'PLIN' => 'Plin', 'MIXTO' => 'Mixto'] as $value => $label)
                                    <option value="{{ $value }}" {{ $defaultPaymentMethod === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-cash-register"></i> Cajón</h5>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="open_drawer_cash" id="open_drawer_cash" value="1"
                            {{ $openDrawerCash ? 'checked' : '' }}>
                        <label class="form-check-label" for="open_drawer_cash">
                            Abrir el cajón automáticamente en pagos en efectivo
                        </label>
                    </div>
                    <small class="text-muted">El botón manual "Caja" del POS seguirá disponible.</small>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                    <a href="{{ route('pos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
