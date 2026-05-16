
<?php $__env->startSection('title', 'Nuevo Comprobante'); ?>
<?php $__env->startSection('page_title', 'Nuevo Comprobante'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Factura/Boleta</h3>
    </div>
    <form id="invoice-form" method="POST" action="<?php echo e(route('invoices.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="company_id" value="<?php echo e($company->id); ?>">
        <input type="hidden" name="customer_id" id="customer_id">
        <input type="hidden" name="customer_data[documento_tipo]" id="customer_data_documento_tipo">
        <input type="hidden" name="customer_data[documento_numero]" id="customer_data_documento_numero">
        <input type="hidden" name="customer_data[nombre]" id="customer_data_nombre">
        <input type="hidden" name="customer_data[direccion]" id="customer_data_direccion">
        <input type="hidden" name="customer_data[ubigeo]" id="customer_data_ubigeo">

        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tipo Documento</label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control" required onchange="updateSerie()">
                            <option value="01">Factura</option>
                            <option value="03">Boleta</option>
                            <option value="NV">Nota de Venta</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Serie</label>
                        <?php if($series->isEmpty()): ?>
                            <div class="text-danger">No hay series disponibles. <a href="<?php echo e(route('series.create', ['company_id' => $company->id])); ?>" class="text-primary">Crear serie</a></div>
                            <select name="serie_id" id="serie_id" class="form-control bg-light" disabled>
                                <option value="">Sin series</option>
                            </select>
                        <?php else: ?>
                            <select name="serie_id" id="serie_id" class="form-control bg-light" required onchange="updateTipoDocumento()">
                                <?php $__currentLoopData = $series; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serie): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($serie->id); ?>" data-tipo="<?php echo e($serie->tipo_documento); ?>" data-serie="<?php echo e($serie->serie); ?>"><?php echo e($serie->serie); ?> (<?php echo e($serie->tipo_documento == '01' ? 'Factura' : 'Boleta'); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha_emision" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
                    </div>
                </div>
                <div class="col-md-2">
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Departamento</label>
                                <select id="inv_departamento" class="form-control" onchange="loadInvProvincias()">
                                    <option value="">Seleccionar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Provincia</label>
                                <select id="inv_provincia" class="form-control" onchange="loadInvDistritos()" disabled>
                                    <option value="">Seleccionar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Distrito</label>
                                <select id="inv_distrito" class="form-control" disabled onchange="updateInvUbigeo()">
                                    <option value="">Seleccionar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cód. Ubigeo</label>
                                <input type="text" id="inv_ubigeo_codigo" class="form-control" readonly>
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
                    <?php if($products->isEmpty()): ?>
                        <div class="alert alert-danger">No hay productos registrados. <a href="<?php echo e(route('products.create', ['company_id' => $company->id])); ?>" class="text-primary">Crear producto</a></div>
                    <?php else: ?>
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Buscar Producto (Código de barras o nombre) <span id="stock-display" class="ml-2"></span></label>
                                <input type="text" id="productSearch" class="form-control" placeholder="Escanee código de barras o escriba nombre..." autocomplete="off">
                                <input type="hidden" id="productSelect" value="">
                                <div id="productResults" class="position-absolute bg-white border rounded mt-1" style="display:none;z-index:1000;max-height:250px;overflow:auto;width:100%;box-shadow:0 4px 8px rgba(0,0,0,0.15);"></div>
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
                    <?php endif; ?>

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
            <a href="<?php echo e(route('invoices.index', ['company_id' => $company->id])); ?>" class="btn btn-secondary">Cancelar</a>
            <button type="button" class="btn btn-primary" onclick="submitInvoiceForm()">Guardar</button>
        </div>
    </form>
</div>

<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Comprobante Procesado</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="success-icon mb-3" style="font-size: 60px; color: #28a745;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 id="invoiceNumberSuccess"></h4>
                <div class="customer-info mb-3">
                    <span id="customerNameSuccess"></span>
                    <br>
                    <span id="paymentMethodSuccess"></span>
                </div>
                <h3>Total: <span id="saleTotalSuccess" style="color: #28a745; font-weight: bold;"></span></h3>
                <input type="hidden" id="lastInvoiceId" value="">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" onclick="sendToSunatFromModal()" id="btnSunatModal">
                    <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                </button>
                <button type="button" class="btn btn-secondary" onclick="printInvoiceFromModal('A4')">
                    <i class="fas fa-file-alt"></i> A4
                </button>
                <button type="button" class="btn btn-info" onclick="printInvoiceFromModal('80mm')">
                    <i class="fas fa-receipt"></i> 80mm
                </button>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" onclick="newInvoice()">
                    <i class="fas fa-plus"></i> Nueva Venta
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
    loadInvDepartamentos();
    
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
const companyId = <?php echo e($company->id); ?>;

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
        document.getElementById('customer_data_ubigeo').value = document.getElementById('inv_ubigeo_codigo').value || '';
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
                if (data.customer.ubigeo) {
                    loadInvUbigeoFromCode(data.customer.ubigeo);
                }
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
                if (data.api_data.ubigeo) {
                    loadInvUbigeoFromCode(data.api_data.ubigeo);
                }
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

let selectedProduct = null;
let productList = <?php echo json_encode($products, 15, 512) ?>;

document.getElementById('productSearch').addEventListener('input', function() {
    const q = this.value.trim();
    const resultsBox = document.getElementById('productResults');
    
    if (q.length < 1) {
        resultsBox.style.display = 'none';
        return;
    }
    
    const isNumeric = /^\d+$/.test(q);
    let results;
    
    if (isNumeric) {
        results = productList.filter(p => (p.codigo_barras && p.codigo_barras.includes(q)) || p.codigo.includes(q));
    } else {
        results = productList.filter(p => p.descripcion.toLowerCase().includes(q.toLowerCase()) || p.codigo.toLowerCase().includes(q.toLowerCase()));
    }
    
    resultsBox.innerHTML = '';
    if (results.length === 0) {
        resultsBox.style.display = 'none';
        return;
    }
    
    results.slice(0, 15).forEach(p => {
        const div = document.createElement('div');
        div.className = 'p-2 border-bottom';
        div.style.cursor = 'pointer';
        const stock = p.stock || 0;
        const stockClass = stock <= 0 ? 'text-danger' : 'text-success';
        div.innerHTML = `<strong>${p.codigo}</strong> - ${p.descripcion} <span class="${stockClass}">(Stock: ${stock})</span> <span class="text-muted">S/ ${parseFloat(p.precio).toFixed(2)}</span>`;
        div.onclick = () => selectProduct(p);
        resultsBox.appendChild(div);
    });
    
    resultsBox.style.display = 'block';
});

document.getElementById('productSearch').addEventListener('blur', () => {
    setTimeout(() => { document.getElementById('productResults').style.display = 'none'; }, 200);
});

document.getElementById('productSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const resultsBox = document.getElementById('productResults');
        if (resultsBox.style.display === 'block' && resultsBox.children.length > 0) {
            resultsBox.children[0].click();
        } else if (this.value.trim()) {
            const q = this.value.trim();
            const isNumeric = /^\d+$/.test(q);
            let results;
            if (isNumeric) {
                results = productList.filter(p => (p.codigo_barras && p.codigo_barras.includes(q)) || p.codigo.includes(q));
            } else {
                results = productList.filter(p => p.descripcion.toLowerCase().includes(q.toLowerCase()) || p.codigo.toLowerCase().includes(q.toLowerCase()));
            }
            if (results.length > 0) selectProduct(results[0]);
        }
    }
});

function selectProduct(p) {
    selectedProduct = p;
    document.getElementById('productSearch').value = p.codigo + ' - ' + p.descripcion;
    document.getElementById('itemPrice').value = parseFloat(p.precio).toFixed(2);
    document.getElementById('productResults').style.display = 'none';
    updateStockDisplay();
}

function updateStockDisplay() {
    if (!selectedProduct) {
        document.getElementById('stock-display').textContent = '';
        return;
    }
    const baseStock = parseInt(selectedProduct.stock) || 0;
    const addedQty = items.filter(i => i.product_id === selectedProduct.id).reduce((sum, i) => sum + i.cantidad, 0);
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
    if (!selectedProduct) { alert('Seleccione un producto'); return; }
    const qty = parseFloat(document.getElementById('itemQty').value);
    const price = Math.round(parseFloat(document.getElementById('itemPrice').value) * 100) / 100;
    const baseStock = parseInt(selectedProduct.stock) || 0;
    
    if (!qty || !price || qty <= 0) { alert('Ingrese cantidad válida'); return; }
    
    const addedQty = items.filter(i => i.product_id === selectedProduct.id).reduce((sum, i) => sum + i.cantidad, 0);
    const availableStock = baseStock - addedQty;
    
    if (availableStock < qty) {
        if (availableStock <= 0) {
            if (!confirm('Stock agotado (0). ¿Desea generar Venta con Stock negativo?')) return;
        } else {
            if (!confirm('Stock insuficiente. Disponible: ' + availableStock + '. ¿Desea generar Venta con Stock negativo?')) return;
        }
    }
    
    items.push({ product_id: selectedProduct.id, codigo: selectedProduct.codigo, descripcion: selectedProduct.descripcion, cantidad: qty, precio: price, stock: baseStock });
    renderItems();
    selectedProduct = null;
    document.getElementById('productSearch').value = '';
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

function loadInvDepartamentos() {
    var deptSelect = document.getElementById('inv_departamento');
    if (!deptSelect) return;
    fetch('/ubigeo/departamentos')
        .then(res => res.json())
        .then(data => {
            deptSelect.innerHTML = '<option value="">Seleccionar</option>';
            data.forEach(dept => {
                var opt = document.createElement('option');
                opt.value = dept;
                opt.textContent = dept;
                deptSelect.appendChild(opt);
            });
        });
}

function loadInvProvincias() {
    var dept = document.getElementById('inv_departamento').value;
    var provSelect = document.getElementById('inv_provincia');
    var distSelect = document.getElementById('inv_distrito');
    provSelect.innerHTML = '<option value="">Seleccionar</option>';
    distSelect.innerHTML = '<option value="">Seleccionar</option>';
    provSelect.disabled = true;
    distSelect.disabled = true;
    document.getElementById('inv_ubigeo_codigo').value = '';
    if (!dept) return;
    fetch('/ubigeo/provincias?departamento=' + encodeURIComponent(dept))
        .then(res => res.json())
        .then(data => {
            provSelect.disabled = false;
            data.forEach(prov => {
                var opt = document.createElement('option');
                opt.value = prov;
                opt.textContent = prov;
                provSelect.appendChild(opt);
            });
        });
}

function loadInvDistritos() {
    var dept = document.getElementById('inv_departamento').value;
    var prov = document.getElementById('inv_provincia').value;
    var distSelect = document.getElementById('inv_distrito');
    distSelect.innerHTML = '<option value="">Seleccionar</option>';
    distSelect.disabled = true;
    document.getElementById('inv_ubigeo_codigo').value = '';
    if (!dept || !prov) return;
    fetch('/ubigeo/distritos?departamento=' + encodeURIComponent(dept) + '&provincia=' + encodeURIComponent(prov))
        .then(res => res.json())
        .then(data => {
            distSelect.disabled = false;
            data.forEach(d => {
                var opt = document.createElement('option');
                opt.value = d.codigo;
                opt.textContent = d.distrito;
                opt.dataset.distrito = d.distrito;
                distSelect.appendChild(opt);
            });
        });
}

function updateInvUbigeo() {
    var distSelect = document.getElementById('inv_distrito');
    var selected = distSelect.options[distSelect.selectedIndex];
    if (selected && selected.value) {
        document.getElementById('inv_ubigeo_codigo').value = selected.value;
    } else {
        document.getElementById('inv_ubigeo_codigo').value = '';
    }
}

function loadInvUbigeoFromCode(codigo) {
    if (!codigo) return;
    var deptSelect = document.getElementById('inv_departamento');
    var provSelect = document.getElementById('inv_provincia');
    var distSelect = document.getElementById('inv_distrito');
    if (!deptSelect || !provSelect || !distSelect) return;
    fetch('/ubigeo/by-codigo?codigo=' + codigo)
        .then(res => res.json())
        .then(data => {
            if (data && data.departamento) {
                deptSelect.value = data.departamento;
                loadInvProvinciasForUbigeo(data.departamento, data.provincia, data.distrito);
            }
        });
}

function loadInvProvinciasForUbigeo(dept, selectedProv, selectedDist) {
    var provSelect = document.getElementById('inv_provincia');
    if (!provSelect) return;
    fetch('/ubigeo/provincias?departamento=' + encodeURIComponent(dept))
        .then(res => res.json())
        .then(data => {
            provSelect.innerHTML = '<option value="">Seleccionar</option>';
            provSelect.disabled = false;
            data.forEach(prov => {
                var opt = document.createElement('option');
                opt.value = prov;
                opt.textContent = prov;
                provSelect.appendChild(opt);
            });
            if (selectedProv) {
                provSelect.value = selectedProv;
                loadInvDistritosForUbigeo(dept, selectedProv, selectedDist);
            }
        });
}

function loadInvDistritosForUbigeo(dept, prov, selectedDist) {
    var distSelect = document.getElementById('inv_distrito');
    if (!distSelect) return;
    fetch('/ubigeo/distritos?departamento=' + encodeURIComponent(dept) + '&provincia=' + encodeURIComponent(prov))
        .then(res => res.json())
        .then(data => {
            distSelect.innerHTML = '<option value="">Seleccionar</option>';
            distSelect.disabled = false;
            var matched = false;
            data.forEach(d => {
                var opt = document.createElement('option');
                opt.value = d.codigo;
                opt.textContent = d.distrito;
                opt.dataset.distrito = d.distrito;
                distSelect.appendChild(opt);
                if (d.distrito.toUpperCase() === selectedDist.toUpperCase()) {
                    distSelect.value = d.codigo;
                    matched = true;
                }
            });
            if (!matched && selectedDist) {
                data.forEach(d => {
                    if (d.codigo === selectedDist) {
                        distSelect.value = d.codigo;
                    }
                });
            }
            if (document.getElementById('inv_ubigeo_codigo')) {
                document.getElementById('inv_ubigeo_codigo').value = distSelect.value;
            }
        });
}

function proceedSubmit() {
    if (items.length === 0) {
        alert('Agregue al menos un producto');
        return;
    }
    const form = document.getElementById('invoice-form');
    
    // Remover inputs de items anteriores si existen
    const oldItems = form.querySelectorAll('input[name^="items["]');
    oldItems.forEach(el => el.remove());
    
    items.forEach((item, idx) => {
        ['product_id', 'codigo', 'descripcion', 'cantidad', 'precio'].forEach(field => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items[' + idx + '][' + field + ']';
            input.value = item[field];
            form.appendChild(input);
        });
    });
    
    if (!document.getElementById('customer_id').value) {
        document.getElementById('customer_data_documento_tipo').value = document.getElementById('doc_tipo').value;
        document.getElementById('customer_data_documento_numero').value = document.getElementById('doc_numero').value;
        document.getElementById('customer_data_nombre').value = document.getElementById('customer_nombre').value;
        document.getElementById('customer_data_direccion').value = document.getElementById('customer_direccion').value;
        document.getElementById('customer_data_ubigeo').value = document.getElementById('inv_ubigeo_codigo').value || '';
    }
    
    // Enviar por AJAX
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success || data.invoice) {
            showSuccessModal(data.invoice || data);
        } else {
            alert(data.message || 'Error al guardar');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        form.submit();
    });
}

function showSuccessModal(invoice) {
    document.getElementById('lastInvoiceId').value = invoice.id;
    document.getElementById('invoiceNumberSuccess').textContent = invoice.full_number || invoice.numero || 'Comprobante #' + invoice.id;
    document.getElementById('customerNameSuccess').textContent = invoice.customer_name || invoice.cliente || 'Cliente Varios';
    document.getElementById('paymentMethodSuccess').textContent = (invoice.metodo_pago || 'EFECTIVO') + (invoice.referencia_pago ? ' - ' + invoice.referencia_pago : '');
    document.getElementById('saleTotalSuccess').textContent = 'S/ ' + parseFloat(invoice.total).toFixed(2);
    
    // Reset botón SUNAT
    const btnSunat = document.getElementById('btnSunatModal');
    btnSunat.disabled = false;
    btnSunat.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar a SUNAT';
    btnSunat.className = 'btn btn-primary';
    
    $('#successModal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

function sendToSunatFromModal() {
    const invoiceId = document.getElementById('lastInvoiceId').value;
    if (!invoiceId) return;
    
    const btn = document.getElementById('btnSunatModal');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    
    fetch('/pos/sunat/' + invoiceId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Enviado';
            btn.className = 'btn btn-success';
            alert(data.message || 'Enviado a SUNAT correctamente');
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar a SUNAT';
            alert(data.message || 'Error al enviar a SUNAT');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar a SUNAT';
        alert('Error al enviar a SUNAT: ' + err);
    });
}

function printInvoiceFromModal(format) {
    const invoiceId = document.getElementById('lastInvoiceId').value;
    if (!invoiceId) return;
    window.open('/pos/print/' + invoiceId + '/' + format, '_blank');
}

function newInvoice() {
    $('#successModal').modal('hide');
    items = [];
    renderItems();
    selectedProduct = null;
    document.getElementById('productSearch').value = '';
    document.getElementById('itemQty').value = '1';
    document.getElementById('itemPrice').value = '';
    document.getElementById('customer_id').value = '';
    document.getElementById('customer_nombre').value = '';
    document.getElementById('customer_direccion').value = '';
    document.getElementById('doc_numero').value = '';
    document.getElementById('customer-status').textContent = '';
    document.getElementById('customer-status').className = 'text-sm';
    document.getElementById('setCustomerBtn').style.display = 'none';
    updateStockDisplay();
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\invoices\create.blade.php ENDPATH**/ ?>