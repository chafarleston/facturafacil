@extends('layouts.admin')
@section('title', 'Restaurante')
@section('page_title', 'Restaurante')

@push('styles')
<style>
    body { overflow: hidden; }
    .main-footer, .content-header { display: none !important; }
    .content-wrapper { padding-top: 0 !important; }
    
    .restaurant-container {
        display: flex;
        height: calc(100vh - 60px);
        width: 100%;
        gap: 10px;
        padding: 10px;
        box-sizing: border-box;
    }
    
    /* Columna Izquierda: Pisos y Mesas (65%) */
    .floors-section {
        width: 65%;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .floors-header {
        padding: 15px;
        border-bottom: 2px solid #eee;
        flex-shrink: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .floors-tabs {
        display: flex;
        gap: 8px;
        padding: 10px 15px;
        background: #f8f9fa;
        flex-shrink: 0;
        overflow-x: auto;
        flex-wrap: wrap;
    }
    
    .floor-tab {
        padding: 8px 20px;
        border-radius: 20px;
        cursor: pointer;
        background: #fff;
        border: 2px solid #ddd;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .floor-tab:hover { border-color: #007bff; }
    .floor-tab.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        padding: 15px;
        overflow-y: auto;
        flex: 1;
        align-content: start;
    }
    
    .table-card {
        background: #fff;
        border: 3px solid #28a745;
        border-radius: 12px;
        padding: 20px 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .table-card:hover { transform: scale(1.03); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .table-card.available { border-color: #28a745; }
    .table-card.occupied { border-color: #dc3545; background: #fff5f5; }
    .table-card.reserved { border-color: #ffc107; background: #fffef0; }
    
    .table-card i { font-size: 28px; margin-bottom: 8px; }
    .table-card.available i { color: #28a745; }
    .table-card.occupied i { color: #dc3545; }
    .table-card.reserved i { color: #ffc107; }
    
    .table-name { font-weight: bold; font-size: 14px; margin-bottom: 4px; }
    .table-capacity { font-size: 11px; color: #666; }
    .table-order {
        font-size: 10px;
        margin-top: 5px;
        padding: 3px 8px;
        border-radius: 10px;
        background: #007bff;
        color: white;
    }
    .table-order.pending { background: #ffc107; color: #333; }
    
    /* Columna Derecha: Productos y Pedido (35%) */
    .order-section {
        width: 35%;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .products-panel {
        flex: 1;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        max-height: 50%;
    }
    
    .products-header {
        padding: 12px 15px;
        background: #f8f9fa;
        border-bottom: 2px solid #eee;
        flex-shrink: 0;
    }
    
    .products-categories {
        display: flex;
        gap: 5px;
        padding: 10px;
        overflow-x: auto;
        flex-shrink: 0;
        background: #fff;
    }
    
    .products-categories-original {
        display: none;
        gap: 5px;
        padding: 10px;
        overflow-x: auto;
        flex-shrink: 0;
        background: #fff;
    }
    
    .category-btn {
        padding: 6px 15px;
        border-radius: 15px;
        border: 1px solid #ddd;
        background: #fff;
        cursor: pointer;
        white-space: nowrap;
        font-size: 12px;
        transition: all 0.2s;
    }
    .category-btn:hover { border-color: #007bff; }
    .category-btn.active { background: #007bff; color: white; border-color: #007bff; }
    
    .products-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
        display: none; /* Oculto hasta seleccionar mesa */
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        align-content: start;
    }
    
    .products-list.active {
        display: grid;
    }
    
    .product-item {
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }
    .product-item:hover { border-color: #007bff; background: #e7f3ff; }
    .product-name { font-size: 11px; font-weight: bold; margin-bottom: 4px; height: 32px; overflow: hidden; }
    .product-price { font-size: 13px; color: #28a745; font-weight: bold; }
    
    .current-order-panel {
        flex: 1;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 200px;
    }
    
    .order-header {
        padding: 12px 15px;
        background: #007bff;
        color: white;
        flex-shrink: 0;
    }
    .order-header h5 { margin: 0; font-size: 14px; }
    .order-header small { font-size: 11px; opacity: 0.9; }
    
    .order-items {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 6px;
        font-size: 12px;
    }
    .order-item-info { flex: 1; }
    .order-item-name { font-weight: bold; }
    .order-item-price { color: #666; font-size: 11px; }
    .order-item-qty { display: flex; align-items: center; gap: 5px; }
    .order-item-qty span { font-weight: bold; min-width: 20px; text-align: center; }
    
    .kitchen-badge {
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 8px;
        margin-left: 5px;
    }
    .kitchen-badge.pending { background: #ffc107; color: #333; }
    .kitchen-badge.sent { background: #17a2b8; color: white; }
    .kitchen-badge.ready { background: #28a745; color: white; }
    .kitchen-badge.delivered { background: #6c757d; color: white; }
    
    .order-empty {
        text-align: center;
        color: #999;
        padding: 30px;
        font-size: 13px;
    }
    .order-empty i { font-size: 30px; margin-bottom: 10px; }
    
    .order-totals {
        padding: 10px 15px;
        border-top: 2px solid #007bff;
        background: #f8f9fa;
    }
    .order-total-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        font-size: 12px;
    }
    .order-total-row.grand { font-size: 16px; font-weight: bold; color: #007bff; margin-top: 5px; padding-top: 5px; border-top: 1px solid #ddd; }
    
    .order-actions {
        padding: 10px;
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .order-actions button { flex: 1; padding: 10px; font-size: 12px; border: none; border-radius: 6px; cursor: pointer; }
    .btn-kitchen { background: #17a2b8; color: white; }
    .btn-kitchen:hover { background: #138496; }
    .btn-print { background: #ffc107; color: #333; }
    .btn-print:hover { background: #e0a800; }
    .btn-close { background: #28a745; color: white; }
    .btn-close:hover { background: #218838; }
    .btn-cancel { background: #dc3545; color: white; }
    .btn-cancel:hover { background: #c82333; }
    
    /* Modal de cantidad */
    .qty-modal .modal-body { padding: 20px; }
    .qty-modal input { font-size: 24px; text-align: center; }
    
    /* Animación para productos nuevos */
    .order-item.new-item {
        animation: highlight 1s ease-out;
    }
    @keyframes highlight {
        0% { background: #d4edda; }
        100% { background: #f8f9fa; }
    }
</style>
@endpush

@section('content')
<div class="restaurant-container">
    {{-- Columna Izquierda: Pisos y Mesas --}}
    <div class="floors-section">
        <div class="floors-header">
            <h5 class="panel-title" style="margin:0;"><i class="fas fa-layer-group"></i> Seleccionar Mesa</h5>
            <a href="{{ route('restaurant.floors.index', ['company_id' => $companyId]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog"></i> Configurar
            </a>
        </div>
        
        <div class="floors-tabs" id="floorsTabs">
            @foreach($floors as $floor)
            <div class="floor-tab {{ $loop->first ? 'active' : '' }}" 
                 data-floor-id="{{ $floor->id }}"
                 onclick="selectFloor({{ $floor->id }})">
                <i class="fas fa-layer-group"></i> {{ $floor->name }}
            </div>
            @endforeach
            @if($floors->isEmpty())
            <div class="alert alert-warning m-2" style="width: 100%;">
                No hay pisos configurados. 
                <a href="{{ route('restaurant.floors.create', ['company_id' => $companyId]) }}">Crear piso</a>
            </div>
            @endif
        </div>
        
        <div class="tables-grid" id="tablesGrid">
            @foreach($floors as $floor)
                @foreach($floor->tables as $table)
                <div class="table-card {{ strtolower($table->status) }} {{ $table->activeOrder() ? 'has-order' : '' }}"
                     data-table-id="{{ $table->id }}"
                     data-floor-id="{{ $floor->id }}"
                     data-order-id="{{ $table->activeOrder()?->id }}"
                     style="{{ $table->status == 'AVAILABLE' ? '' : 'display:none' }}"
                     onclick="selectTable({{ $table->id }})">
                    <i class="fas fa-chair"></i>
                    <div class="table-name">{{ $table->name }}</div>
                    <div class="table-capacity"><i class="fas fa-users"></i> {{ $table->capacity }}</div>
                    @if($table->activeOrder())
                    <div class="table-order">{{ $table->activeOrder()->order_number }}</div>
                    @endif
                </div>
                @endforeach
            @endforeach
        </div>
    </div>
    
    {{-- Columna Derecha: Productos y Pedido --}}
    <div class="order-section">
        <div class="products-panel">
            <div class="products-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold"><i class="fas fa-utensils"></i> Productos</span>
                    <span id="selectedTableLabel" class="text-muted" style="font-size: 12px;">Seleccione una mesa</span>
                </div>
            </div>
            <div class="products-categories" id="productsCategories">
                <button class="category-btn active" data-category="all" onclick="filterProducts('all')">Todos</button>
                @foreach($categories as $category)
                <button class="category-btn" data-category="{{ $category->id }}" onclick="filterProducts({{ $category->id }})">
                    {{ $category->nombre }}
                </button>
                @endforeach
            </div>
            <div class="products-list" id="productsList" style="display: none;">
                @foreach($products as $product)
                <div class="product-item" 
                     data-product-id="{{ $product->id }}"
                     data-category-id="{{ $product->category_id }}"
                     data-product-name="{{ $product->descripcion }}"
                     data-product-price="{{ $product->precio }}"
                     onclick="addProductToOrder({{ $product->id }})">
                    <div class="product-name">{{ $product->descripcion }}</div>
                    <div class="product-price">S/ {{ number_format($product->precio, 2) }}</div>
                </div>
                @endforeach
                @if($products->isEmpty())
                <div class="alert alert-warning" style="grid-column: span 2;">
                    No hay productos. <a href="{{ route('products.create', ['company_id' => $companyId]) }}">Crear producto</a>
                </div>
                @endif
            </div>
        </div>
        
        <div class="current-order-panel">
            <div class="order-header">
                <h5><i class="fas fa-receipt"></i> Pedido Actual</h5>
                <small id="currentOrderInfo">Sin pedido seleccionado</small>
            </div>
            <div class="order-items" id="orderItems">
                <div class="order-empty">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Seleccione una mesa y agregue productos</p>
                </div>
            </div>
            <div class="order-totals" id="orderTotals" style="display: none;">
                <div class="order-total-row">
                    <span>Subtotal:</span>
                    <span id="orderSubtotal">S/ 0.00</span>
                </div>
                <div class="order-total-row">
                    <span>IGV (18%):</span>
                    <span id="orderIgv">S/ 0.00</span>
                </div>
                <div class="order-total-row grand">
                    <span>TOTAL:</span>
                    <span id="orderTotal">S/ 0.00</span>
                </div>
            </div>
            <div class="order-actions" id="orderActions" style="display: none;">
                <button class="btn-kitchen" onclick="sendToKitchen()">
                    <i class="fas fa-paper-plane"></i> Cocina
                </button>
                <button class="btn-print" onclick="printKitchenTicket()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button class="btn-close" onclick="closeTable()">
                    <i class="fas fa-check"></i> Cerrar
                </button>
            </div>
            <div class="order-actions">
                <button class="btn-cancel" onclick="cancelOrder()" style="flex: 1;" id="btnCancelOrder" disabled>
                    <i class="fas fa-times"></i> Anular Pedido
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cantidad --}}
<div class="modal fade qty-modal" id="qtyModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cantidad</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="number" id="itemQtyInput" class="form-control" value="1" min="0.1" step="0.1">
                <small class="text-muted">Producto: <span id="modalProductName"></span></small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddItem()">Agregar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentOrderId = null;
let currentTableId = null;
let productsData = @json($products);
let allFloors = @json($floors);
let pendingProductId = null;
let previousTableBorderColor = {};

function selectFloor(floorId) {
    document.querySelectorAll('.floor-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.floor-tab[data-floor-id="${floorId}"]`).classList.add('active');
    
    // Mostrar todas las mesas, no ocultar las de otros pisos
    document.querySelectorAll('.table-card').forEach(card => {
        card.style.display = '';
    });
}

function selectTable(tableId) {
    const table = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
    if (!table) return;
    
    const tableName = table.querySelector('.table-name').textContent;
    const orderId = table.dataset.orderId;
    
    // Si ya hay un pedido activo y es otra mesa, confirmar cambio
    if (currentOrderId && currentTableId && currentTableId != tableId) {
        if (!confirm('Hay un pedido activo en otra mesa. ¿Cambiar de mesa?')) {
            return;
        }
        // Restaurar estilo de mesa anterior
        resetTableStyle(currentTableId);
    }
    
    // Marcar mesa como seleccionada
    if (!previousTableBorderColor[tableId]) {
        previousTableBorderColor[tableId] = table.style.borderColor || '';
    }
    table.style.borderColor = '#007bff';
    table.style.borderWidth = '4px';
    
    currentTableId = tableId;
    document.getElementById('selectedTableLabel').textContent = 'Mesa: ' + tableName;
    
    // MOSTRAR productos y categorías al seleccionar mesa
    document.getElementById('productsCategories').style.display = 'flex';
    document.getElementById('productsList').style.display = 'grid';
    
    if (orderId) {
        loadOrder(orderId);
    } else {
        openTable(tableId);
    }
}

function resetTableStyle(tableId) {
    const table = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
    if (table && previousTableBorderColor[tableId]) {
        table.style.borderColor = previousTableBorderColor[tableId];
        table.style.borderWidth = '3px';
    }
}

function openTable(tableId) {
    document.getElementById('currentOrderInfo').textContent = 'Abriendo mesa...';
    document.getElementById('btnCancelOrder').disabled = false;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/restaurant/tables/' + tableId + '/open', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log('Open table result:', data);
        if (data.success) {
            currentOrderId = data.order_id;
            document.querySelector(`.table-card[data-table-id="${tableId}"]`).dataset.orderId = data.order_id;
            document.getElementById('currentOrderInfo').textContent = 'Pedido: ' + (data.order_number || ' #' + data.order_id);
            renderOrder({ items: [], subtotal: 0, igv: 0, total: 0 });
        } else {
            alert(data.message || 'Error al abrir mesa');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error al abrir mesa: ' + err.message);
    });
}

function loadOrder(orderId) {
    document.getElementById('currentOrderInfo').textContent = 'Cargando pedido...';
    
    fetch('/restaurant/orders/' + orderId)
    .then(res => res.json())
    .then(data => {
        console.log('Load order result:', data);
        if (data.success) {
            currentOrderId = orderId;
            const order = data.order;
            document.getElementById('currentOrderInfo').textContent = 'Pedido: ' + order.order_number;
            renderOrder(order);
        } else {
            alert(data.message || 'Error al cargar pedido');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error al cargar pedido: ' + err.message);
    });
}

function renderOrder(order) {
    const container = document.getElementById('orderItems');
    
    if (!order.items || order.items.length === 0) {
        container.innerHTML = '<div class="order-empty"><i class="fas fa-shopping-basket"></i><p>Seleccione una mesa y agregue productos</p></div>';
        document.getElementById('orderTotals').style.display = 'none';
        document.getElementById('orderActions').style.display = 'none';
        return;
    }
    
    let html = '';
    order.items.forEach(item => {
        const statusClass = item.kitchen_status.toLowerCase();
        const statusLabel = {
            'PENDING': 'Pendiente',
            'SENT': 'Enviado',
            'READY': 'Listo',
            'DELIVERED': 'Entregado'
        }[item.kitchen_status] || item.kitchen_status;
        
        html += `<div class="order-item" data-item-id="${item.id}">
            <div class="order-item-info">
                <div class="order-item-name">
                    ${item.product_name}
                    <span class="kitchen-badge ${statusClass}">${statusLabel}</span>
                </div>
                <div class="order-item-price">S/ ${parseFloat(item.unit_price).toFixed(2)} x ${item.quantity}</div>
            </div>
            <div class="order-item-qty">
                <button class="btn btn-sm btn-outline-secondary" onclick="updateItemQty(${item.id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button class="btn btn-sm btn-outline-success" onclick="updateItemQty(${item.id}, 1)">+</button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${item.id})"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
    
    const subtotal = parseFloat(order.subtotal) || 0;
    const igv = parseFloat(order.igv) || 0;
    const total = parseFloat(order.total) || 0;
    
    document.getElementById('orderSubtotal').textContent = 'S/ ' + subtotal.toFixed(2);
    document.getElementById('orderIgv').textContent = 'S/ ' + igv.toFixed(2);
    document.getElementById('orderTotal').textContent = 'S/ ' + total.toFixed(2);
    document.getElementById('orderTotals').style.display = '';
    document.getElementById('orderActions').style.display = 'flex';
}

function filterProducts(categoryId) {
    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
    const activeBtn = document.querySelector(`.category-btn[data-category="${categoryId}"]`);
    if (activeBtn) activeBtn.classList.add('active');
    
    document.querySelectorAll('.product-item').forEach(item => {
        if (categoryId === 'all' || item.dataset.categoryId == categoryId) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function addProductToOrder(productId) {
    if (!currentOrderId) {
        alert('Seleccione una mesa primero');
        return;
    }
    
    pendingProductId = productId;
    const product = productsData.find(p => p.id === productId);
    document.getElementById('modalProductName').textContent = product.descripcion;
    document.getElementById('itemQtyInput').value = 1;
    document.getElementById('itemQtyInput').min = 0.1;
    document.getElementById('itemQtyInput').step = 0.1;
    $('#qtyModal').modal('show');
}

function confirmAddItem() {
    const quantity = parseFloat(document.getElementById('itemQtyInput').value);
    if (!quantity || quantity <= 0) {
        alert('Ingrese una cantidad válida');
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    console.log('Adding item to order:', currentOrderId, 'Product:', pendingProductId, 'Qty:', quantity);
    
    fetch('/restaurant/orders/' + currentOrderId + '/items', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: pendingProductId,
            quantity: quantity
        })
    })
    .then(async res => {
        const text = await res.text();
        console.log('Response status:', res.status);
        console.log('Response text:', text);
        
        if (!res.ok) {
            throw new Error('HTTP ' + res.status + ': ' + text);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Invalid JSON: ' + text);
        }
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            loadOrder(currentOrderId);
            $('#qtyModal').modal('hide');
        } else {
            alert(data.message || 'Error al agregar producto');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error al agregar producto: ' + err.message);
    });
}

function updateItemQty(itemId, change) {
    const itemEl = document.querySelector(`.order-item[data-item-id="${itemId}"]`);
    const qtySpan = itemEl.querySelector('.order-item-qty span');
    let newQty = parseFloat(qtySpan.textContent) + change;
    
    if (newQty <= 0) {
        removeItem(itemId);
        return;
    }
    
    fetch('/restaurant/orders/items/' + itemId, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ quantity: newQty })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            qtySpan.textContent = newQty;
            loadOrder(currentOrderId);
        }
    });
}

function removeItem(itemId) {
    if (!confirm('¿Eliminar este producto?')) return;
    
    fetch('/restaurant/orders/items/' + itemId, { method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadOrder(currentOrderId);
        }
    });
}

function sendToKitchen() {
    if (!currentOrderId) return;
    
    fetch('/restaurant/orders/' + currentOrderId + '/send-to-kitchen', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            loadOrder(currentOrderId);
        }
    });
}

function printKitchenTicket() {
    if (!currentOrderId) return;
    window.open('/restaurant/orders/' + currentOrderId + '/print-kitchen', '_blank');
}

function closeTableSilently() {
    if (!currentOrderId) return;
    
    fetch('/restaurant/orders/' + currentOrderId + '/close', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json());
    
    resetOrderUI();
}

function closeTable() {
    if (!currentOrderId) return;
    
    if (!confirm('¿Cerrar la mesa? El pedido se completará.')) return;
    
    fetch('/restaurant/orders/' + currentOrderId + '/close', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Mesa cerrada exitosamente');
            resetOrderUI();
            location.reload();
        }
    });
}

function resetOrderUI() {
    currentOrderId = null;
    currentTableId = null;
    
    document.getElementById('currentOrderInfo').textContent = 'Sin pedido seleccionado';
    document.getElementById('selectedTableLabel').textContent = 'Seleccione una mesa';
    document.getElementById('orderItems').innerHTML = '<div class="order-empty"><i class="fas fa-shopping-basket"></i><p>Seleccione una mesa y agregue productos</p></div>';
    document.getElementById('orderTotals').style.display = 'none';
    document.getElementById('orderActions').style.display = 'none';
    document.getElementById('btnCancelOrder').disabled = true;
    document.getElementById('productsCategories').style.display = 'none';
    document.getElementById('productsList').style.display = 'none';
    
    // Restaurar estilo de mesa
    Object.keys(previousTableBorderColor).forEach(tableId => {
        resetTableStyle(parseInt(tableId));
    });
}

function cancelOrder() {
    if (!currentOrderId) return;
    
    if (!confirm('¿Anular este pedido? Se eliminarán todos los productos.')) return;
    
    fetch('/restaurant/orders/' + currentOrderId + '/cancel', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            resetOrderUI();
            location.reload();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (allFloors.length > 0) {
        selectFloor(allFloors[0].id);
    }
});
</script>
@endpush
