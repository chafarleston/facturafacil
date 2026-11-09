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

                    <h5 class="mb-3"><i class="fas fa-motorcycle"></i> Delivery</h5>

                    <input type="hidden" name="delivery_enabled" value="0">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="delivery_enabled" id="delivery_enabled" value="1"
                            {{ old('delivery_enabled', $deliveryEnabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="delivery_enabled">Habilitar delivery en el Autopedido</label>
                    </div>
                    <small class="form-text text-muted mb-3">
                        Si está desactivado, el Autopedido solo permite recoger en el local. Si está activo, el cliente
                        puede elegir recojo o delivery y pagar el costo de la zona seleccionada.
                    </small>

                    @error('zones')
                        <div class="alert alert-danger py-2 mt-3">{{ $message }}</div>
                    @enderror

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 90px;">Zona</th>
                                    <th>Nombre</th>
                                    <th style="width: 150px;">Costo (S/)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deliveryZones as $index => $zone)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <input type="text" name="zones[{{ $index }}][name]"
                                                class="form-control form-control-sm @error('zones.'.$index.'.name') is-invalid @enderror"
                                                maxlength="100" value="{{ old('zones.'.$index.'.name', $zone['name'] ?? '') }}"
                                                placeholder="Ej. Centro">
                                            @error('zones.'.$index.'.name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="number" name="zones[{{ $index }}][price]"
                                                class="form-control form-control-sm @error('zones.'.$index.'.price') is-invalid @enderror"
                                                min="0" max="9999.99" step="0.01"
                                                value="{{ old('zones.'.$index.'.price', number_format((float) ($zone['price'] ?? 0), 2, '.', '')) }}">
                                            @error('zones.'.$index.'.price')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">
                        Las zonas sin nombre no aparecen en el Autopedido. Los precios incluyen IGV.
                    </small>

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

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-message"></i> Mensajes de confirmación</h5>
                    <div class="form-group">
                        <label for="success_ticket_text">Texto: ticket imprimiéndose</label>
                        <input type="text" name="success_ticket_text" id="success_ticket_text" class="form-control" maxlength="200"
                               value="{{ old('success_ticket_text', $successTicketText) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="success_pay_text">Texto: pase a caja para pagar</label>
                        <input type="text" name="success_pay_text" id="success_pay_text" class="form-control" maxlength="200"
                               value="{{ old('success_pay_text', $successPayText) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="success_prepare_text">Texto: preparación después del pago</label>
                        <input type="text" name="success_prepare_text" id="success_prepare_text" class="form-control" maxlength="200"
                               value="{{ old('success_prepare_text', $successPrepareText) }}" required>
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
