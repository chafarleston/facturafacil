@extends('layouts.admin')
@section('title', 'Nuevo Comprobante')
@section('page_title', 'Nuevo Comprobante')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Factura/Boleta</h3>
    </div>
    <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $company->id }}">
        <input type="hidden" name="customer_id" id="customer_id">
        <input type="hidden" name="customer_data[documento_tipo]" id="customer_data_documento_tipo">
        <input type="hidden" name="customer_data[documento_numero]" id="customer_data_documento_numero">
        <input type="hidden" name="customer_data[nombre]" id="customer_data_nombre">
        <input type="hidden" name="customer_data[direccion]" id="customer_data_direccion">

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo Documento</label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control" required onchange="updateSerie()">
                            <option value="01">Factura</option>
                            <option value="03">Boleta</option>
                            <option value="NV">Nota de Venta</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Serie</label>
                        @if($series->isEmpty())
                            <div class="text-danger">No hay series disponibles. <a href="{{ route('series.create', ['company_id' => $company->id]) }}" class="text-primary">Crear serie</a></div>
                            <select name="serie_id" id="serie_id" class="form-control bg-light" disabled>
                                <option value="">Sin series</option>
                            </select>
                        @else
                            <select name="serie_id" id="serie_id" class="form-control bg-light" required onchange="updateTipoDocumento()">
                                @foreach($series as $serie)
                                    <option value="{{ $serie->id }}" data-tipo="{{ $serie->tipo_documento }}" data-serie="{{ $serie->serie }}">{{ $serie->serie }} ({{ $serie->tipo_documento == '01' ? 'Factura' : 'Boleta' }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha_emision" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Método de Pago</label>
                        <select name="metodo_pago" id="metodo_pago" class="form-control" onchange="updateReferencia()">
                            <option value="EFECTIVO">Efectivo</option>
                            <option value="TARJETA">Tarjeta</option>
                            <option value="YAPE">Yape</option>
                            <option value="PLIN">Plin</option>
                            <option value="DEPOSITO">Depósito</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Referencia (opcional)</label>
                        <input type="text" name="referencia_pago" id="referencia_pago" class="form-control" placeholder="N° operación / código">
                    </div>
                </div>
            </div>

            <div class="card card-secondary mb-3">
                <div class="card-header">
                    <h4 class="card-title">Datos del Cliente</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select id="doc_tipo" class="form-control">
                                    <option value="1">DNI</option>
                                    <option value="6">RUC</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Número</label>
                                <div class="input-group">
                                    <input type="text" id="doc_numero" class="form-control" maxlength="11">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" onclick="buscarCliente()"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" id="customer_nombre" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" id="customer_direccion" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span id="customer-status" class="text-sm"></span>
                            <button type="button" id="setCustomerBtn" class="btn btn-warning btn-sm ml-2" style="display:none;" onclick="setCustomer()">Establecer Cliente</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-secondary mb-3">
                <div class="card-header">
                    <h4 class="card-title">Agregar Producto</h4>
                </div>
                <div class="card-body">
                    @if($products->isEmpty())
                        <div class="alert alert-danger">No hay productos registrados. <a href="{{ route('products.create', ['company_id' => $company->id]) }}" class="text-primary">Crear producto</a></div>
                    @else
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Producto <span id="stock-display" class="ml-2"></span></label>
                                <select id="productSelect" class="form-control">
                                    <option value="">Seleccionar producto</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->precio }}" data-code="{{ $product->codigo }}" data-name="{{ $product->descripcion }}" data-stock="{{ $product->stock }}">
                                            {{ $product->codigo }} - {{ $product->descripcion }} - S/ {{ number_format($product->precio, 2) }} (Stock: {{ $product->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>Cantidad</label>
                                <input type="number" id="itemQty" class="form-control" value="1" min="0.01" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>Precio</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">S/</span>
                                    </div>
                                    <input type="number" id="itemPrice" class="form-control" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-success" onclick="agregarItem()"><i class="fas fa-plus"></i> Agregar</button>
                        </div>
                    </div>
                    @endif

                    <table class="table table-bordered mt-3">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Precio</th>
                                <th class="text-right">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="invoice-items"></tbody>
                    </table>
                </div>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-4">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-right"><strong>Subtotal:</strong></td>
                            <td class="text-right" id="subtotal">0.00</td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>IGV (18%):</strong></td>
                            <td class="text-right" id="igv">0.00</td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>Total:</strong></td>
                            <td class="text-right"><strong id="total">0.00</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('invoices.index', ['company_id' => $company->id]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="button" class="btn btn-primary" onclick="submitInvoiceForm()">Guardar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateSerie() {
    const tipoDoc = document.getElementById('tipo_documento').value;
    const serieSelect = document.getElementById('serie_id');
    if (!serieSelect) return;
    const options = Array.from(serieSelect.options);
    const preferredCode = tipoDoc === '01' ? 'F001' : (tipoDoc === '03' ? 'B001' : (tipoDoc === 'NV' ? 'NV01' : null));
    if (preferredCode) {
        for (let idx = 0; idx < options.length; idx++) {
            const opt = options[idx];
            const serieCode = opt.getAttribute('data-serie');
            if (serieCode === preferredCode) {
                serieSelect.selectedIndex = idx;
                validateDocForTipo(tipoDoc);
                return;
            }
        }
    }
    for (let idx = 0; idx < options.length; idx++) {
        const opt = options[idx];
        const serieTipo = opt.getAttribute('data-tipo');
        if (serieTipo === tipoDoc) {
            serieSelect.selectedIndex = idx;
            validateDocForTipo(tipoDoc);
            return;
        }
    }
}

function validateDocForTipo(tipoDoc) {
    const docNumero = document.getElementById('doc_numero').value.trim();
    const docTipo = document.getElementById('doc_tipo').value;
    const statusEl = document.getElementById('customer-status');
    
    if (tipoDoc === '01') {
        if (docNumero && docNumero.length !== 11) {
            statusEl.textContent = '⚠️ Las facturas requieren RUC de 11 dígitos';
            statusEl.className = 'text-sm text-danger';
        } else if (docNumero && docTipo !== '6') {
            statusEl.textContent = '⚠️ Las facturas requieren tipo RUC (6)';
            statusEl.className = 'text-sm text-danger';
        } else {
            statusEl.textContent = '';
            statusEl.className = 'text-sm';
        }
    } else {
        statusEl.textContent = '';
        statusEl.className = 'text-sm';
    }
}

function updateTipoDocumento() {
    const serieSelect = document.getElementById('serie_id');
    const selectedOption = serieSelect.options[serieSelect.selectedIndex];
    const serieTipo = selectedOption.getAttribute('data-tipo');
    document.getElementById('tipo_documento').value = serieTipo;
    validateDocForTipo(serieTipo);
}

document.addEventListener('DOMContentLoaded', function() {
    updateSerie();
    updateStockDisplay();
    updateReferencia();
    
    document.getElementById('doc_numero').addEventListener('input', function() {
        const tipoDoc = document.getElementById('tipo_documento').value;
        validateDocForTipo(tipoDoc);
    });
    
    document.getElementById('doc_tipo').addEventListener('change', function() {
        const tipoDoc = document.getElementById('tipo_documento').value;
        const docTipo = this.value;
        const docNumero = document.getElementById('doc_numero');
        if (docTipo === '6') {
            docNumero.maxLength = 11;
        } else {
            docNumero.maxLength = 8;
        }
        validateDocForTipo(tipoDoc);
    });
});

let items = [];
const companyId = {{ $company->id }};

function submitInvoiceForm() {
    const tipoDoc = document.getElementById('tipo_documento').value;
    const docNumero = document.getElementById('doc_numero').value.trim();
    const docTipo = document.getElementById('doc_tipo').value;
    const customerId = document.getElementById('customer_id').value;
    
    // Si es Factura (01), siempre verificar RUC
    if (tipoDoc === '01') {
        // Si hay cliente seleccionado, verificar que sea RUC
        if (customerId) {
            // Ya hay un cliente seleccionado, validar que sea RUC de 11 dígitos
            fetch('/decolecta/search?company_id=' + companyId + '&documento=' + docNumero)
            .then(res => res.json())
            .then(data => {
                if (data.exists && data.customer) {
                    if (data.customer.documento_tipo !== '6' || data.customer.documento_numero.length !== 11) {
                        alert('Las facturas requieren cliente con RUC de 11 dígitos. Seleccione un cliente con RUC.');
                        return;
                    }
                }
                proceedSubmit();
            });
            return;
        }
        // No hay cliente, validar campos nuevos
        if (docNumero.length !== 11) {
            alert('Las facturas requieren RUC de 11 dígitos');
            return;
        }
        if (docTipo !== '6') {
            alert('Las facturas requieren tipo de documento RUC (6)');
            return;
        }
    }
    
    proceedSubmit();
}

function proceedSubmit() {
    if (items.length === 0) {
        alert('Agregue al menos un producto');
        return;
    }
    const form = document.getElementById('invoice-form');
    items.forEach((item, idx) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'items[' + idx + '][product_id]';
        input.value = item.product_id;
        form.appendChild(input);
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'items[' + idx + '][codigo]';
        input2.value = item.codigo;
        form.appendChild(input2);
        const input3 = document.createElement('input');
        input3.type = 'hidden';
        input3.name = 'items[' + idx + '][descripcion]';
        input3.value = item.descripcion;
        form.appendChild(input3);
        const input4 = document.createElement('input');
        input4.type = 'hidden';
        input4.name = 'items[' + idx + '][cantidad]';
        input4.value = item.cantidad;
        form.appendChild(input4);
        const input5 = document.createElement('input');
        input5.type = 'hidden';
        input5.name = 'items[' + idx + '][precio]';
        input5.value = item.precio;
        form.appendChild(input5);
    });
    if (!document.getElementById('customer_id').value) {
        document.getElementById('customer_data_documento_tipo').value = document.getElementById('doc_tipo').value;
        document.getElementById('customer_data_documento_numero').value = document.getElementById('doc_numero').value;
        document.getElementById('customer_data_nombre').value = document.getElementById('customer_nombre').value;
        document.getElementById('customer_data_direccion').value = document.getElementById('customer_direccion').value;
    }
    form.submit();
}

function cleanAddress(direccion) {
    if (!direccion) return '';
    direccion = direccion.replace(/(\s*-\s*)+/g, ' - ');
    direccion = direccion.trim();
    if (direccion.endsWith(' -')) {
        direccion = direccion.slice(0, -2).trim();
    }
    return direccion;
}

function checkFacturaRequired() {
    const tipoDoc = document.getElementById('tipo_documento').value;
    const docTipo = document.getElementById('doc_tipo').value;
    const docNumero = document.getElementById('doc_numero').value.trim();
    
    if (tipoDoc === '01') {
        if (docNumero && docNumero.length !== 11) {
            return 'Las facturas requieren RUC de 11 dígitos';
        }
        if (docTipo !== '6' && docNumero) {
            return 'Las facturas requieren tipo de documento RUC (6)';
        }
    }
    return null;
}

function buscarCliente() {
    const errorMsg = checkFacturaRequired();
    if (errorMsg) {
        alert(errorMsg);
        return;
    }
    
    const docNumero = document.getElementById('doc_numero').value.trim();
    const docTipo = document.getElementById('doc_tipo').value;
    const statusEl = document.getElementById('customer-status');
    if (!docNumero) { alert('Ingrese número de documento'); return; }
    statusEl.textContent = 'Buscando...';
    statusEl.className = 'text-sm text-info';
    fetch('/decolecta/search?company_id=' + companyId + '&documento=' + docNumero)
        .then(res => res.json())
        .then(data => {
            if (data.found && data.exists) {
                document.getElementById('customer_id').value = data.customer.id;
                document.getElementById('customer_nombre').value = data.customer.nombre;
                document.getElementById('customer_direccion').value = cleanAddress(data.customer.direccion) || '';
                document.getElementById('doc_tipo').value = data.customer.documento_tipo;
                document.getElementById('customer_data_documento_tipo').value = data.customer.documento_tipo;
                document.getElementById('customer_data_documento_numero').value = data.customer.documento_numero;
                document.getElementById('customer_data_nombre').value = data.customer.nombre;
                document.getElementById('customer_data_direccion').value = cleanAddress(data.customer.direccion) || '';
                statusEl.textContent = '✓ Cliente encontrado';
                statusEl.className = 'text-sm text-success';
            } else if (data.api_data) {
                document.getElementById('customer_nombre').value = data.api_data.nombre || '';
                document.getElementById('customer_direccion').value = cleanAddress(data.api_data.direccion) || '';
                document.getElementById('doc_tipo').value = data.api_data.documento_tipo || docTipo;
                document.getElementById('customer_data_documento_tipo').value = data.api_data.documento_tipo || docTipo;
                document.getElementById('customer_data_documento_numero').value = data.api_data.documento_numero || docNumero;
                document.getElementById('customer_data_nombre').value = data.api_data.nombre || '';
                document.getElementById('customer_data_direccion').value = cleanAddress(data.api_data.direccion) || '';
                statusEl.textContent = 'Datos cargados. Presione "Establecer Cliente"';
                statusEl.className = 'text-sm text-warning';
                document.getElementById('setCustomerBtn').style.display = 'inline-block';
            } else {
                statusEl.textContent = 'Cliente no encontrado';
                statusEl.className = 'text-sm text-danger';
            }
        })
        .catch(err => { statusEl.textContent = 'Error al buscar'; statusEl.className = 'text-sm text-danger'; });
}

function setCustomer() {
    const docNumero = document.getElementById('doc_numero').value;
    const docTipo = document.getElementById('doc_tipo').value;
    const nombre = document.getElementById('customer_nombre').value;
    const direccion = document.getElementById('customer_direccion').value;
    if (!docNumero || !nombre) { alert('Ingrese número de documento y nombre'); return; }
    document.getElementById('customer_data_documento_tipo').value = docTipo;
    document.getElementById('customer_data_documento_numero').value = docNumero;
    document.getElementById('customer_data_nombre').value = nombre;
    document.getElementById('customer_data_direccion').value = direccion;
    document.getElementById('customer-status').textContent = '✓ Cliente establecido';
    document.getElementById('customer-status').className = 'text-sm text-success';
    document.getElementById('setCustomerBtn').style.display = 'none';
}

function updateReferencia() {
    const metodo = document.getElementById('metodo_pago').value;
    const refInput = document.getElementById('referencia_pago');
    
    if (metodo === 'EFECTIVO') {
        refInput.value = '';
        refInput.placeholder = 'No requiere referencia';
        refInput.disabled = true;
    } else if (metodo === 'YAPE' || metodo === 'PLIN') {
        refInput.placeholder = 'Ingrese número de operación';
        refInput.disabled = false;
    } else if (metodo === 'TARJETA') {
        refInput.placeholder = 'Ingrese últimos 4 dígitos';
        refInput.disabled = false;
    } else {
        refInput.placeholder = 'N° operación / código';
        refInput.disabled = false;
    }
}

document.getElementById('productSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    document.getElementById('itemPrice').value = option.dataset.price || '';
    updateStockDisplay();
});

function updateStockDisplay() {
    const select = document.getElementById('productSelect');
    const option = select.options[select.selectedIndex];
    if (!option.value) {
        document.getElementById('stock-display').textContent = '';
        return;
    }
    const baseStock = parseInt(option.dataset.stock) || 0;
    const addedQty = items.filter(i => i.product_id === option.value).reduce((sum, i) => sum + i.cantidad, 0);
    const availableStock = baseStock - addedQty;
    const displayEl = document.getElementById('stock-display');
    if (availableStock <= 0) {
        displayEl.textContent = '⚠️ Stock: 0';
        displayEl.className = 'text-danger font-weight-bold ml-2';
    } else {
        displayEl.textContent = 'Disp: ' + availableStock;
        displayEl.className = 'text-success ml-2';
    }
}

function agregarItem() {
    const select = document.getElementById('productSelect');
    const option = select.options[select.selectedIndex];
    if (!option.value) { alert('Seleccione un producto'); return; }
    const qty = parseFloat(document.getElementById('itemQty').value);
    const price = Math.round(parseFloat(document.getElementById('itemPrice').value) * 100) / 100;
    const baseStock = parseInt(option.dataset.stock) || 0;
    
    if (!qty || !price || qty <= 0) { alert('Ingrese cantidad válida'); return; }
    
    // Calcular stock ya agregado en esta factura para este producto
    const addedQty = items.filter(i => i.product_id === option.value).reduce((sum, i) => sum + i.cantidad, 0);
    const availableStock = baseStock - addedQty;
    
    // Verificar stock disponible
    if (availableStock < qty) {
        if (availableStock <= 0) {
            if (!confirm('Stock agotado (0). ¿Desea generar Venta con Stock negativo?')) {
                return;
            }
        } else {
            if (!confirm('Stock insuficiente. Disponible: ' + availableStock + '. ¿Desea generar Venta con Stock negativo?')) {
                return;
            }
        }
    }
    
    items.push({ product_id: option.value, codigo: option.dataset.code, descripcion: option.dataset.name, cantidad: qty, precio: price, stock: baseStock });
    renderItems();
    select.value = '';
    document.getElementById('itemQty').value = '1';
    document.getElementById('itemPrice').value = '';
    updateStockDisplay();
}

function removeItem(index) {
    items.splice(index, 1);
    renderItems();
    updateStockDisplay();
}

function renderItems() {
    const tbody = document.getElementById('invoice-items');
    tbody.innerHTML = '';
    let totalConIgv = 0;
    items.forEach((item, index) => {
        const itemTotal = Math.round(item.cantidad * item.precio * 100) / 100;
        const row = document.createElement('tr');
        row.innerHTML = '<td>' + item.codigo + '</td><td>' + item.descripcion + '</td><td class="text-right">' + item.cantidad + '</td><td class="text-right">' + item.precio.toFixed(2) + '</td><td class="text-right">' + itemTotal.toFixed(2) + '</td><td><button type="button" onclick="removeItem(' + index + ')" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>';
        tbody.appendChild(row);
        totalConIgv += itemTotal;
    });
    const subtotal = Math.round(totalConIgv / 1.18 * 100) / 100;
    const igv = Math.round((totalConIgv - subtotal) * 100) / 100;
    const total = Math.round((subtotal + igv) * 100) / 100;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('igv').textContent = igv.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
}
</script>
@endpush