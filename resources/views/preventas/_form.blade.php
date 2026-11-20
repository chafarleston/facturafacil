@php
    $editando = isset($preventa);
    $esCotizacion = in_array($tipo, [\App\Models\Preventa::TIPO_COTIZACION, \App\Models\Preventa::TIPO_PEDIDO], true);
    $estadoActual = old('estado', $preventa->estado ?? (in_array($tipo, ['pedido', 'soporte'], true) ? 'PENDIENTE' : 'BORRADOR'));
    $fechaActual = old('fecha', isset($preventa) ? $preventa->fecha?->format('Y-m-d') : now()->format('Y-m-d'));
    $vencimientoActual = old('fecha_vencimiento', isset($preventa) ? $preventa->fecha_vencimiento?->format('Y-m-d') : '');
    $documentoActual = old('document_type', $preventa->document_type ?? '03');
    $condicionActual = old('payment_condition', $preventa->payment_condition ?? 'CONTADO');
    $diasActual = old('credit_days', $preventa->credit_days ?? 7);
    $itemsIniciales = old('items', isset($preventa) ? $preventa->items->map(fn ($item) => [
        'product_id' => $item->product_id,
        'cantidad' => (float) $item->cantidad,
        'precio_unitario' => (float) $item->precio_unitario,
    ])->values()->all() : []);
@endphp

<form method="POST"
      action="{{ $editando ? route('preventas.update', $preventa) : route('preventas.store') }}">
    @csrf
    @if($editando)
        @method('PUT')
    @else
        <input type="hidden" name="tipo" value="{{ $tipo }}">
    @endif

    <div class="row">
        <div class="col-md-3 form-group">
            <label>Tipo</label>
            <input type="text" class="form-control" value="{{ $tipos[$tipo] }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label>Número</label>
            <input type="text" class="form-control" value="{{ $preventa->numero ?? 'Se generará al guardar' }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label for="estado">Estado <span class="text-danger">*</span></label>
            <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach($estados as $value => $label)
                    <option value="{{ $value }}" {{ $estadoActual === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="monto">Monto total (S/)</label>
            <input type="number" step="0.01" min="0" name="monto" id="monto"
                   class="form-control @error('monto') is-invalid @enderror"
                   value="{{ old('monto', $preventa->monto ?? '') }}" {{ $esCotizacion ? 'readonly' : '' }}>
            @error('monto')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="cliente_nombre">Cliente <span class="text-danger">*</span></label>
            <input type="text" name="cliente_nombre" id="cliente_nombre"
                   class="form-control @error('cliente_nombre') is-invalid @enderror"
                   value="{{ old('cliente_nombre', $preventa->cliente_nombre ?? '') }}" required>
            @error('cliente_nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-2 form-group">
            <label for="cliente_documento">DNI / RUC</label>
            <input type="text" name="cliente_documento" id="cliente_documento" maxlength="20"
                   class="form-control @error('cliente_documento') is-invalid @enderror"
                   value="{{ old('cliente_documento', $preventa->cliente_documento ?? '') }}">
            @error('cliente_documento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="cliente_email">Correo</label>
            <input type="email" name="cliente_email" id="cliente_email"
                   class="form-control @error('cliente_email') is-invalid @enderror"
                   value="{{ old('cliente_email', $preventa->cliente_email ?? '') }}">
            @error('cliente_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="cliente_telefono">Teléfono</label>
            <input type="text" name="cliente_telefono" id="cliente_telefono"
                   class="form-control @error('cliente_telefono') is-invalid @enderror"
                   value="{{ old('cliente_telefono', $preventa->cliente_telefono ?? '') }}">
            @error('cliente_telefono')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    @if($esCotizacion)
        <div class="row">
            <div class="col-md-5 form-group">
                <label for="preventaCustomerId">Cliente registrado</label>
                <select id="preventaCustomerId" name="customer_id" class="form-control @error('customer_id') is-invalid @enderror">
                    <option value="">Cliente manual</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}"
                            {{ (string) old('customer_id', $preventa->customer_id ?? '') === (string) $customer->id ? 'selected' : '' }}>
                            {{ $customer->nombre }} · {{ $customer->documento_numero }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small class="form-text text-muted">
                    <a href="{{ route('customers.create') }}" target="_blank" rel="noopener">Crear nuevo cliente</a>
                </small>
            </div>
            <div class="col-md-3 form-group">
                <label for="document_type">Tipo de venta <span class="text-danger">*</span></label>
                <select name="document_type" id="document_type" class="form-control @error('document_type') is-invalid @enderror" required>
                    <option value="03" {{ $documentoActual === '03' ? 'selected' : '' }}>Boleta</option>
                    <option value="01" {{ $documentoActual === '01' ? 'selected' : '' }}>Factura</option>
                </select>
                @error('document_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-2 form-group">
                <label for="payment_condition">Pago <span class="text-danger">*</span></label>
                <select name="payment_condition" id="payment_condition" class="form-control @error('payment_condition') is-invalid @enderror" required>
                    <option value="CONTADO" {{ $condicionActual === 'CONTADO' ? 'selected' : '' }}>Contado</option>
                    <option value="CREDITO" {{ $condicionActual === 'CREDITO' ? 'selected' : '' }}>Crédito</option>
                </select>
                @error('payment_condition')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-2 form-group" id="creditDaysGroup">
                <label for="credit_days">Plazo</label>
                <select name="credit_days" id="credit_days" class="form-control @error('credit_days') is-invalid @enderror">
                    @foreach([7, 15, 30, 45] as $dias)
                        <option value="{{ $dias }}" {{ (string) $diasActual === (string) $dias ? 'selected' : '' }}>{{ $dias }} días</option>
                    @endforeach
                </select>
                @error('credit_days')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos</h5>
            <button type="button" class="btn btn-success btn-sm" onclick="addItemRow()">
                <i class="fas fa-plus"></i> Agregar producto
            </button>
        </div>
        @error('items')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="itemsTable">
                <thead>
                    <tr>
                        <th style="min-width:320px;">Producto</th>
                        <th style="width:120px;">Cantidad</th>
                        <th style="width:130px;" class="text-right">Precio unit.</th>
                        <th style="width:130px;" class="text-right">Total</th>
                        <th style="width:45px;"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-sm">
                    <tr><th>Subtotal</th><td class="text-right" id="preventaSubtotal">S/ 0.00</td></tr>
                    <tr><th>IGV</th><td class="text-right" id="preventaIgv">S/ 0.00</td></tr>
                    <tr><th>Total</th><td class="text-right font-weight-bold" id="preventaTotal">S/ 0.00</td></tr>
                </table>
            </div>
        </div>
    @endif

    <div class="form-group">
        <label for="titulo">Título / asunto <span class="text-danger">*</span></label>
        <input type="text" name="titulo" id="titulo"
               class="form-control @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $preventa->titulo ?? '') }}" required>
        @error('titulo')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="descripcion">Descripción / detalle</label>
            <textarea name="descripcion" id="descripcion" rows="5"
                      class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $preventa->descripcion ?? '') }}</textarea>
            @error('descripcion')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="observaciones">Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="5"
                      class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $preventa->observaciones ?? '') }}</textarea>
            @error('observaciones')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <label for="fecha">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" id="fecha"
                   class="form-control @error('fecha') is-invalid @enderror"
                   value="{{ $fechaActual }}" required>
            @error('fecha')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="fecha_vencimiento">Fecha de vencimiento</label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
                   class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                   value="{{ $vencimientoActual }}">
            @error('fecha_vencimiento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('preventas.index', ['tipo' => $tipo]) }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

@if($esCotizacion)
<script>
const preventaCustomers = @json($customers);
const preventaProducts = @json($products);
const preventaIgvDefault = {{ (float) $igvPercent }};
let preventaItemIndex = 0;

function preventaEscape(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (character) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
    });
}

function preventaProductOptions(selectedId) {
    let html = '<option value="">Seleccione un producto...</option>';
    preventaProducts.forEach(function(product) {
        html += '<option value="' + product.id + '"'
            + ' data-price="' + product.precio + '"'
            + ' data-igv="' + (product.igv_percent ?? preventaIgvDefault) + '"'
            + (String(selectedId) === String(product.id) ? ' selected' : '')
            + '>' + preventaEscape((product.codigo ? product.codigo + ' - ' : '') + product.descripcion) + '</option>';
    });
    return html;
}

function addItemRow(item) {
    item = item || {};
    const index = preventaItemIndex++;
    const row = document.createElement('tr');
    row.className = 'preventa-item-row';
    row.innerHTML = '<td><select name="items[' + index + '][product_id]" class="form-control form-control-sm item-product" onchange="updateItemRow(this)">'
        + preventaProductOptions(item.product_id) + '</select></td>'
        + '<td><input type="number" name="items[' + index + '][cantidad]" class="form-control form-control-sm item-quantity" min="0.0001" step="0.0001" value="' + (item.cantidad || 1) + '" oninput="recalculateItems()"></td>'
        + '<td><input type="number" class="form-control form-control-sm item-price" value="' + (item.precio_unitario || 0) + '" readonly></td>'
        + '<td class="text-right item-total">S/ 0.00</td>'
        + '<td><button type="button" class="btn btn-danger btn-xs" onclick="removeItemRow(this)"><i class="fas fa-times"></i></button></td>';
    document.querySelector('#itemsTable tbody').appendChild(row);
    const select = row.querySelector('.item-product');
    if (item.product_id) {
        select.value = String(item.product_id);
    }
    updateItemRow(select);
}

function updateItemRow(select) {
    const row = select.closest('tr');
    const option = select.options[select.selectedIndex];
    const price = parseFloat(option?.dataset.price) || 0;
    row.querySelector('.item-price').value = price.toFixed(2);
    recalculateItems();
}

function removeItemRow(button) {
    button.closest('tr').remove();
    recalculateItems();
}

function recalculateItems() {
    let subtotal = 0;
    let igv = 0;
    let total = 0;
    document.querySelectorAll('.preventa-item-row').forEach(function(row) {
        const select = row.querySelector('.item-product');
        const option = select.options[select.selectedIndex];
        const price = parseFloat(option?.dataset.price) || 0;
        const rate = parseFloat(option?.dataset.igv ?? preventaIgvDefault) || 0;
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const lineTotal = Math.round(quantity * price * 100) / 100;
        const lineSubtotal = rate > 0 ? Math.round((lineTotal / (1 + rate / 100)) * 100) / 100 : lineTotal;
        row.querySelector('.item-price').value = price.toFixed(2);
        row.querySelector('.item-total').textContent = 'S/ ' + lineTotal.toFixed(2);
        total += lineTotal;
        subtotal += lineSubtotal;
        igv += lineTotal - lineSubtotal;
    });
    document.getElementById('preventaSubtotal').textContent = 'S/ ' + subtotal.toFixed(2);
    document.getElementById('preventaIgv').textContent = 'S/ ' + igv.toFixed(2);
    document.getElementById('preventaTotal').textContent = 'S/ ' + total.toFixed(2);
    document.getElementById('monto').value = total.toFixed(2);
}

function updateCreditFields() {
    const condition = document.getElementById('payment_condition').value;
    const days = document.getElementById('credit_days');
    const group = document.getElementById('creditDaysGroup');
    group.style.display = condition === 'CREDITO' ? 'block' : 'none';
    days.required = condition === 'CREDITO';
    if (condition === 'CREDITO') {
        const date = document.getElementById('fecha').value;
        if (date) {
            const due = new Date(date + 'T00:00:00');
            due.setDate(due.getDate() + (parseInt(days.value) || 7));
            document.getElementById('fecha_vencimiento').value = due.toISOString().slice(0, 10);
        }
    }
}

document.getElementById('preventaCustomerId').addEventListener('change', function() {
    const customer = preventaCustomers.find(function(item) {
        return String(item.id) === String(this.value);
    }.bind(this));
    if (!customer) return;
    document.getElementById('cliente_nombre').value = customer.nombre || '';
    document.getElementById('cliente_documento').value = customer.documento_numero || '';
    document.getElementById('cliente_email').value = customer.email || '';
    document.getElementById('cliente_telefono').value = customer.telefono || '';
});

document.getElementById('payment_condition').addEventListener('change', updateCreditFields);
document.getElementById('credit_days').addEventListener('change', updateCreditFields);
document.getElementById('fecha').addEventListener('change', updateCreditFields);
document.addEventListener('DOMContentLoaded', function() {
    const initialItems = @json($itemsIniciales);
    if (initialItems.length) {
        initialItems.forEach(addItemRow);
    } else {
        addItemRow();
    }
    updateCreditFields();
});
</script>
@endif
