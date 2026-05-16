<?php $__env->startSection('title', 'Punto de Venta'); ?>
<?php $__env->startSection('page_title', 'Punto de Venta'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    body { overflow: hidden; }
    .main-footer, .content-header { display: none !important; }
    .content-wrapper { padding-top: 0 !important; }
    
    .pos-container {
        display: flex;
        height: calc(100vh - 60px);
        width: 100%;
        gap: 10px;
        padding: 10px;
        box-sizing: border-box;
    }
    
    /* Columna 1: 65% Categorías/Productos */
    .categories-section {
        width: 65%;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .categories-header {
        padding: 15px;
        border-bottom: 2px solid #eee;
        flex-shrink: 0;
    }
    
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        padding: 15px;
        overflow-y: auto;
        height: 100%;
    }
    
    .category-card {
        background: #fff;
        border-radius: 15px;
        padding: 25px 15px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        border: 3px solid transparent;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .category-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .category-card i {
        font-size: 32px;
        margin-bottom: 10px;
    }
    .category-card h5 {
        margin: 0;
        font-size: 13px;
        font-weight: bold;
    }
    .category-card small {
        font-size: 10px;
        color: #666;
        margin-top: 5px;
    }
    
    /* Productos */
    .products-section {
        display: none;
        flex-direction: column;
        height: 100%;
    }
    
    .products-header {
        padding: 10px 15px;
        background: #f8f9fa;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        padding: 15px 15px 30px 15px;
        overflow-y: auto;
        flex: 1;
        align-content: start;
        min-height: 0;
    }
    
    .product-card {
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        min-height: 85px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .product-card:hover {
        border-color: #007bff;
        transform: scale(1.03);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .product-name {
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 6px;
        height: 32px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.2;
    }
    .product-price {
        font-size: 14px;
        color: #28a745;
        font-weight: bold;
    }
    .product-stock {
        font-size: 10px;
        color: #666;
        margin-top: 4px;
    }
    
    /* Espaciador para la última fila */
    .products-grid::after {
        content: '';
        height: 20px;
    }
    
    /* Columna 2: 35% Venta */
    .sale-section {
        width: 35%;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .sale-items-panel {
        flex: 1.5;
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        min-height: 250px;
    }
    
    .sale-data-panel {
        flex: 0;
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-height: 280px;
        max-height: 350px;
        overflow-y: auto;
    }
    
    .panel-title {
        font-weight: bold;
        font-size: 15px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid #eee;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .sale-items-list {
        flex: 1;
        overflow-y: auto;
    }
    
    .sale-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 6px;
    }
    .sale-item-info { flex: 1; }
    .sale-item-name { font-weight: bold; font-size: 13px; }
    .sale-item-price { font-size: 12px; color: #666; }
    .sale-item-actions { display: flex; align-items: center; gap: 6px; }
    .qty-btn {
        width: 26px;
        height: 26px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .qty-minus { background: #dc3545; color: white; }
    .qty-plus { background: #28a745; color: white; }
    .sale-item-qty { font-weight: bold; min-width: 25px; text-align: center; font-size: 13px; }
    .remove-item { color: #dc3545; cursor: pointer; margin-left: 5px; }
    .remove-item:hover { color: #c82333; }
    
    .empty-sale { text-align: center; color: #999; padding: 30px; }
    .empty-sale i { font-size: 40px; margin-bottom: 10px; }
    
    .customer-dropdown {
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        width: calc(100% - 30px);
    }
    .customer-option {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .customer-option:hover { background: #f8f9fa; }
    .customer-option:last-child { border-bottom: none; }
    .customer-option-name { font-weight: bold; font-size: 13px; }
    .customer-option-doc { font-size: 11px; color: #666; }
    
    .sale-totals {
        border-top: 2px solid #007bff;
        padding-top: 10px;
        margin: 12px 0;
    }
    .sale-total-row { 
        display: flex; 
        justify-content: space-between; 
        padding: 4px 0; 
        font-size: 13px; 
    }
    .sale-total-row.grand-total {
        font-size: 20px;
        font-weight: bold;
        color: #007bff;
        border-top: 2px solid #ddd;
        padding-top: 10px;
        margin-top: 8px;
    }
    
    .btn-pay {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border: none;
        padding: 15px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 10px;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s;
    }
    .btn-pay:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    }
    .btn-pay:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }
    
    .btn-cancel {
        background: #dc3545;
        color: white;
        border: none;
        padding: 10px;
        font-size: 13px;
        border-radius: 8px;
        cursor: pointer;
        flex: 0 0 auto;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="pos-container">
    <div class="categories-section">
        <div class="categories-header">
            <h5 class="panel-title" style="margin:0;"><i class="fas fa-th-large"></i> Seleccionar Categoria</h5>
        </div>
        
        <div class="categories-grid" id="categoriesGrid">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="category-card" style="border-color: <?php echo e($category->color ?? '#007bff'); ?>;"
                 onclick="showProducts(<?php echo e($category->id); ?>, '<?php echo e($category->nombre); ?>')">
                <i class="<?php echo e($category->icon ?? 'fas fa-tag'); ?>" style="color: <?php echo e($category->color ?? '#007bff'); ?>;"></i>
                <h5 style="color: #333;"><?php echo e($category->nombre); ?></h5>
                <small><?php echo e($category->products->where('estado', 'ACTIVO')->count()); ?> productos</small>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        
        <div class="products-section" id="productsSection">
            <div class="products-header">
                <button class="btn btn-sm btn-secondary" onclick="backToCategories()">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <span class="ml-3 font-weight-bold" id="categoryTitle"></span>
            </div>
            <div class="products-grid" id="productsGrid"></div>
        </div>
    </div>
    
    <div class="sale-section">
        <div class="sale-items-panel">
            <div class="panel-title"><i class="fas fa-shopping-cart"></i> Productos</div>
            <div class="sale-items-list" id="saleItems">
                <div class="empty-sale">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Agrega productos</p>
                </div>
            </div>
        </div>
        
        <div class="sale-data-panel">
            <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                <div style="flex: 1; position: relative;">
                    <label><i class="fas fa-user"></i> Cliente</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="customerSearch" class="form-control form-control-sm" placeholder="Buscar..." autocomplete="off" style="flex: 1;">
                        <button type="button" class="btn btn-sm btn-success" onclick="openCustomerModal()" title="Nuevo cliente" style="padding: 4px 10px;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <input type="hidden" id="customerId" value="">
                    <div id="customerDropdown" class="customer-dropdown" style="display: none;"></div>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label>Tipo Documento</label>
                    <select id="documentType" class="form-control form-control-sm" onchange="updateSerieByType()">
                        <option value="03">BOLETA</option>
                        <option value="01">FACTURA</option>
                        <option value="NV">NOTA DE VENTA</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label>Serie</label>
                    <input type="text" id="serieDisplay" class="form-control form-control-sm" readonly disabled>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                <div style="flex: 1;">
                    <label>Metodo de Pago</label>
                    <select id="paymentMethod" class="form-control form-control-sm">
                        <option value="EFECTIVO">EFECTIVO</option>
                        <option value="TARJETA">TARJETA</option>
                        <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                        <option value="YAPE">YAPE</option>
                        <option value="PLIN">PLIN</option>
                        <option value="MIXTO">MIXTO</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label>Referencia</label>
                    <input type="text" id="reference" class="form-control form-control-sm" placeholder="N° operacion">
                </div>
            </div>
            
            <div class="sale-totals">
                <div class="sale-total-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">S/ 0.00</span>
                </div>
                <div class="sale-total-row">
                    <span>IGV (18%):</span>
                    <span id="igv">S/ 0.00</span>
                </div>
                <div class="sale-total-row grand-total">
                    <span>TOTAL:</span>
                    <span id="total">S/ 0.00</span>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button class="btn-cancel" onclick="cancelSale()" style="flex: 0 0 100px;">
                    <i class="fas fa-trash"></i> Cancelar
                </button>
                <button class="btn-pay" id="btnPay" onclick="processSale()" disabled style="flex: 1;">
                    <i class="fas fa-credit-card"></i> COBRAR
                </button>
            </div>
        </div>
    </div>
</div>

<form id="saleForm" method="POST" action="<?php echo e(route('pos.store')); ?>" style="display: none;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="customer_id" id="customerIdInput">
    <input type="hidden" name="document_type" id="documentTypeInput">
    <input type="hidden" name="payment_method" id="paymentMethodInput">
    <input type="hidden" name="reference" id="referenceInput">
    <input type="hidden" name="items_json" id="itemsJson">
    <input type="hidden" name="total" id="totalInput">
</form>

<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Venta Procesada</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <h4 id="invoiceNumberSuccess"></h4>
                <h3>Total: <span id="saleTotalSuccess"></span></h3>
                <input type="hidden" id="lastInvoiceId" value="">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" onclick="sendToSunat()">
                    <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                </button>
                <button type="button" class="btn btn-secondary" onclick="printInvoice('A4')">
                    <i class="fas fa-file-alt"></i> A4
                </button>
                <button type="button" class="btn btn-secondary" onclick="printInvoice('80mm')">
                    <i class="fas fa-receipt"></i> 80mm
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Error</h5>
            </div>
            <div class="modal-body text-center">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0; height: 500px;">
                <iframe id="customerFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let saleItems = [];
const productsData = <?php echo json_encode($products->where('estado', 'ACTIVO'), 512) ?>;
const categoriesData = <?php echo json_encode($categories, 15, 512) ?>;
const customersData = <?php echo json_encode($customers, 15, 512) ?>;
const seriesData = <?php echo json_encode($series, 15, 512) ?>;

function searchCustomers(term) {
    if (term.length < 2) {
        document.getElementById('customerDropdown').style.display = 'none';
        return;
    }
    
    const termLower = term.toLowerCase();
    const results = customersData.filter(c => {
        const nombreMatch = c.nombre && c.nombre.toLowerCase().includes(termLower);
        const documentoMatch = c.documento_numero && c.documento_numero.includes(term);
        return nombreMatch || documentoMatch;
    });
    
    if (results.length === 0) {
        document.getElementById('customerDropdown').innerHTML = '<div class="customer-option"><span class="text-muted">Sin resultados</span></div>';
        document.getElementById('customerDropdown').style.display = 'block';
        return;
    }
    
    let html = '';
    results.slice(0, 10).forEach(customer => {
        html += '<div class="customer-option" onclick="selectCustomer(' + customer.id + ', \'' + customer.nombre.replace(/'/g, "\\'") + '\')">' +
            '<div class="customer-option-name">' + customer.nombre + '</div>' +
            '<div class="customer-option-doc">' + (customer.documento_tipo || '') + ': ' + (customer.documento_numero || '') + '</div>' +
        '</div>';
    });
    
    document.getElementById('customerDropdown').innerHTML = html;
    document.getElementById('customerDropdown').style.display = 'block';
}

function selectCustomer(id, nombre) {
    document.getElementById('customerId').value = id;
    document.getElementById('customerSearch').value = nombre;
    document.getElementById('customerDropdown').style.display = 'none';
}

function updateSerieByType() {
    const docType = document.getElementById('documentType').value;
    const typePrefixes = { '01': 'F', '03': 'B', 'NV': 'NV' };
    const prefix = typePrefixes[docType] || 'F';
    let defaultSerie = prefix + '001';
    
    if (seriesData && seriesData.length > 0) {
        const matchingSerie = seriesData.find(s => s.tipo_documento === docType);
        if (matchingSerie && matchingSerie.serie) {
            defaultSerie = matchingSerie.serie;
        }
    }
    
    document.getElementById('serieDisplay').value = defaultSerie;
}

function showProducts(categoryId, categoryName) {
    document.getElementById('categoriesGrid').style.display = 'none';
    document.getElementById('productsSection').style.display = 'flex';
    document.getElementById('categoryTitle').textContent = categoryName;
    
    const products = productsData.filter(p => p.category_id === categoryId);
    
    if (products.length === 0) {
        document.getElementById('productsGrid').innerHTML = '<div class="empty-sale"><i class="fas fa-box-open"></i><p>Sin productos</p></div>';
        return;
    }
    
    let html = '';
    products.forEach(product => {
        html += '<div class="product-card" onclick="addToSale(' + product.id + ')">' +
            '<div class="product-name">' + product.descripcion + '</div>' +
            '<div class="product-price">S/ ' + parseFloat(product.precio).toFixed(2) + '</div>' +
            '<div class="product-stock">Stock: ' + product.stock + '</div>' +
        '</div>';
    });
    document.getElementById('productsGrid').innerHTML = html;
}

function backToCategories() {
    document.getElementById('categoriesGrid').style.display = 'grid';
    document.getElementById('productsSection').style.display = 'none';
}

function addToSale(productId) {
    const product = productsData.find(p => p.id === productId);
    if (!product) return;
    
    if (product.stock <= 0) {
        showError('Sin stock');
        return;
    }
    
    const existingItem = saleItems.find(item => item.id === productId);
    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
        } else {
            showError('Stock insuficiente');
            return;
        }
    } else {
        saleItems.push({
            id: product.id,
            name: product.descripcion,
            price: parseFloat(product.precio),
            quantity: 1,
            stock: product.stock
        });
    }
    
    renderSaleItems();
}

function decreaseQty(productId) {
    const item = saleItems.find(item => item.id === productId);
    if (item) {
        if (item.quantity > 1) {
            item.quantity--;
        } else {
            saleItems = saleItems.filter(i => i.id !== productId);
        }
    }
    renderSaleItems();
}

function increaseQty(productId) {
    const item = saleItems.find(item => item.id === productId);
    if (item && item.quantity < item.stock) {
        item.quantity++;
        renderSaleItems();
    }
}

function removeItem(productId) {
    saleItems = saleItems.filter(i => i.id !== productId);
    renderSaleItems();
}

function cancelSale() {
    if (saleItems.length > 0 && confirm('¿Cancelar venta?')) {
        saleItems = [];
        renderSaleItems();
    }
}

function renderSaleItems() {
    const container = document.getElementById('saleItems');
    
    if (saleItems.length === 0) {
        container.innerHTML = '<div class="empty-sale"><i class="fas fa-shopping-basket"></i><p>Agrega productos</p></div>';
        document.getElementById('btnPay').disabled = true;
        return;
    }
    
    let html = '';
    saleItems.forEach(item => {
        html += '<div class="sale-item">' +
            '<div class="sale-item-info">' +
                '<div class="sale-item-name">' + item.name + '</div>' +
                '<div class="sale-item-price">S/ ' + item.price.toFixed(2) + ' x ' + item.quantity + '</div>' +
            '</div>' +
            '<div class="sale-item-actions">' +
                '<button class="qty-btn qty-minus" onclick="decreaseQty(' + item.id + ')">-</button>' +
                '<span class="sale-item-qty">' + item.quantity + '</span>' +
                '<button class="qty-btn qty-plus" onclick="increaseQty(' + item.id + ')">+</button>' +
                '<i class="fas fa-times remove-item" onclick="removeItem(' + item.id + ')"></i>' +
            '</div>' +
        '</div>';
    });
    
    container.innerHTML = html;
    document.getElementById('btnPay').disabled = false;
    calculateTotals();
}

function calculateTotals() {
    let total = 0;
    saleItems.forEach(item => {
        total += item.price * item.quantity;
    });
    
    const base = total / 1.18;
    const igv = total - base;
    
    document.getElementById('subtotal').textContent = 'S/ ' + base.toFixed(2);
    document.getElementById('igv').textContent = 'S/ ' + igv.toFixed(2);
    document.getElementById('total').textContent = 'S/ ' + total.toFixed(2);
}

function getTotal() {
    let total = 0;
    saleItems.forEach(item => {
        total += item.price * item.quantity;
    });
    return total;
}

function processSale() {
    if (saleItems.length === 0) {
        showError('No hay productos');
        return;
    }
    
    document.getElementById('customerIdInput').value = document.getElementById('customerId').value;
    document.getElementById('documentTypeInput').value = document.getElementById('documentType').value;
    document.getElementById('paymentMethodInput').value = document.getElementById('paymentMethod').value;
    document.getElementById('referenceInput').value = document.getElementById('reference').value;
    document.getElementById('itemsJson').value = JSON.stringify(saleItems);
    document.getElementById('totalInput').value = getTotal();
    
    document.getElementById('saleForm').submit();
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    $('#errorModal').modal('show');
}

function sendToSunat() {
    const invoiceId = document.getElementById('lastInvoiceId').value;
    if (!invoiceId) return;
    
    fetch('/pos/sunat/' + invoiceId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || (data.success ? 'Enviado' : 'Error'));
    })
    .catch(error => {
        alert('Error al enviar: ' + error);
    });
}

function printInvoice(format) {
    const invoiceId = document.getElementById('lastInvoiceId').value;
    if (!invoiceId) return;
    window.open('/pos/print/' + invoiceId + '/' + format, '_blank');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cancelSale();
});

document.getElementById('customerSearch').addEventListener('input', function(e) {
    searchCustomers(e.target.value);
});

document.getElementById('customerSearch').addEventListener('blur', function() {
    setTimeout(() => {
        document.getElementById('customerDropdown').style.display = 'none';
    }, 200);
});

document.addEventListener('DOMContentLoaded', function() {
    updateSerieByType();
});

function openCustomerModal() {
    const companyId = 1;
    document.getElementById('customerFrame').src = '/customers/create?company_id=' + companyId;
    $('#customerModal').modal('show');
}

function onCustomerCreated(customer) {
    customersData.push(customer);
    $('#customerModal').modal('hide');
    selectCustomer(customer.id, customer.nombre);
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\facturafacil\resources\views\pos\index.blade.php ENDPATH**/ ?>