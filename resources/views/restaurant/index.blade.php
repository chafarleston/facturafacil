@extends('layouts.admin')
@section('title', 'Restaurante')
@section('page_title', 'Restaurante')

@push('styles')
<style>
    body { overflow: hidden; }
    .main-footer, .content-header { display: none !important; }
    .content-wrapper { padding-top: 0 !important; }
    
    .restaurant-container {
        height: calc(100vh - 60px);
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
    }
    
    .floors-section {
        height: 100%;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .floors-header {
        padding: 12px 15px;
        border-bottom: 2px solid #eee;
        flex-shrink: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .floors-header h4 { margin: 0; font-size: 16px; }
    .floors-header small { font-size: 11px; color: #666; }
    
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
        padding: 6px 16px;
        border-radius: 20px;
        cursor: pointer;
        background: #fff;
        border: 2px solid #ddd;
        transition: all 0.2s;
        white-space: nowrap;
        font-size: 13px;
    }
    .floor-tab:hover { border-color: #007bff; }
    .floor-tab.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
        padding: 15px;
        overflow-y: auto;
        flex: 1;
        align-content: start;
    }
    
    .table-card {
        background: #fff;
        border: 3px solid #28a745;
        border-radius: 12px;
        padding: 15px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        min-height: 90px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .table-card:hover { transform: scale(1.03); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .table-card.available { border-color: #28a745; }
    .table-card.occupied { border-color: #dc3545; background: #fff5f5; }
    .table-card.reserved { border-color: #ffc107; background: #fffef0; }
    
    .table-card i { font-size: 24px; margin-bottom: 6px; }
    .table-card.available i { color: #28a745; }
    .table-card.occupied i { color: #dc3545; }
    .table-card.reserved i { color: #ffc107; }
    
    .table-name { font-weight: bold; font-size: 13px; margin-bottom: 2px; }
    .table-capacity { font-size: 10px; color: #666; }
    .table-order {
        font-size: 9px;
        margin-top: 4px;
        padding: 2px 6px;
        border-radius: 10px;
        background: #007bff;
        color: white;
    }
    
    /* Modal Table Order - Full screen on mobile */
    .table-order-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        z-index: 9999;
        flex-direction: column;
    }
    
    .table-order-modal.show {
        display: flex;
    }
    
    .modal-header-bar {
        padding: 12px 15px;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }
    
    .modal-header-bar h4 { margin: 0; font-size: 16px; }
    
    .btn-close-modal {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 5px 10px;
    }
    
    .modal-tabs {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        flex-shrink: 0;
    }
    
    .modal-tab {
        flex: 1;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
    }
    .modal-tab:hover { background: #e9ecef; }
    .modal-tab.active {
        border-bottom-color: #007bff;
        color: #007bff;
    }
    
    .modal-content-area {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }
    
    /* Tab Products */
    .products-categories {
        display: grid;
        grid-template-rows: repeat(2, auto);
        grid-auto-flow: column;
        gap: 8px;
        padding: 8px 0;
        overflow-x: auto;
        overflow-y: hidden;
        flex-shrink: 0;
    }
    .products-categories::-webkit-scrollbar { height: 4px; }
    .products-categories::-webkit-scrollbar-thumb { background: #ccc; border-radius: 2px; }
    
    .category-btn {
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid #ddd;
        background: #fff;
        cursor: pointer;
        white-space: nowrap;
        font-size: 12px;
        transition: all 0.2s;
    }
    .category-btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        padding: 10px 0;
    }
    
    .product-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .product-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    }
    
    .product-name { font-size: 12px; font-weight: 600; margin-bottom: 4px; }
    .product-price { font-size: 14px; color: #28a745; font-weight: bold; }
    
    /* Tab Order */
    .order-items-list {
        padding: 0;
    }
    
    .order-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
    }
    
    .order-item-info { flex: 1; }
    .order-item-name { font-weight: 600; }
    .order-item-qty { font-size: 11px; color: #666; }
    
    .order-item-actions {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    
    .btn-qty-change {
        width: 28px;
        height: 28px;
        border: 1px solid #ddd;
        border-radius: 50%;
        background: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .btn-qty-change:hover { background: #007bff; color: white; border-color: #007bff; }
    
.btn-remove-item {
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 50%;
        background: #dc3545;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }
    .btn-note-item {
        width: 28px;
        height: 28px;
        border: 1px solid #007bff;
        border-radius: 50%;
        background: #fff;
        color: #007bff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }
    .btn-note-item:hover { background: #007bff; color: white; }
    
    .order-item-note {
        font-size: 11px;
        color: #ff9800;
        font-style: italic;
        margin-top: 3px;
    }
    
    .modal-actions {
        padding: 10px;
        background: #f8f9fa;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        flex-shrink: 0;
    }
    
    .btn-action {
        flex: 1;
        min-width: 100px;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        text-align: center;
        font-size: 12px;
    }
    
    .btn-kitchen { background: #ff9800; color: white; }
    .btn-print { background: #17a2b8; color: white; }
    .btn-send { background: #ffc107; color: #212529; }
    .btn-close-order { background: #28a745; color: white; }
    .btn-cancel-order { background: #dc3545; color: white; }
    .btn-move { background: #6f42c1; color: white; }
    .btn-cash-drawer { background: #fd7e14; color: white; }
    .move-table-item { display:flex; align-items:center; padding:10px 12px; margin-bottom:6px; border-radius:8px; cursor:pointer; transition:all .2s; border:2px solid transparent; }
    .move-table-item:hover { border-color: #007bff; background: #f0f7ff; }
    .move-table-item.occupied { border-color: #ffc107; background: #fffef5; }
    .move-table-item.available { border-color: #28a745; background: #f0fff4; }
    .move-table-item .table-icon { font-size:20px; margin-right:12px; width:24px; text-align:center; }
    .btn-charge { background: #28a745; color: white; }
    .btn-charge:hover { background: #218838; }
    .btn-prebill { background: #17a2b8; color: white; }
    .prebill-option { padding:8px 14px; cursor:pointer; font-size:13px; transition:background .15s; }
    .prebill-option:hover { background: #e3f2fd; color: #007bff; }
    .btn-prebill:hover { background: #138496; }
    .customer-option { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee; }
    .customer-option:hover { background: #f0f9ff; }
    .customer-option-name { font-weight: 600; font-size: 13px; }
    .customer-option-doc { font-size: 11px; color: #666; }
    
    .order-totals-box {
        padding: 15px;
        background: #f8f9fa;
        border-top: 2px solid #ddd;
    }
    
    .order-total-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 13px;
    }
    
    .order-total-row.grand {
        font-size: 18px;
        font-weight: bold;
        border-top: 2px solid #333;
        margin-top: 8px;
        padding-top: 10px;
    }
    
    .order-empty {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    .order-empty i { font-size: 48px; margin-bottom: 10px; }
    .order-empty p { font-size: 14px; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .tables-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            padding: 10px;
        }
        .table-card { min-height: 80px; padding: 12px 8px; }
        .table-name { font-size: 12px; }
        .products-grid { grid-template-columns: repeat(2, 1fr); }
        .modal-tabs { font-size: 13px; }
    }
</style>
@endpush

@section('content')
<div class="restaurant-container">
    <div class="floors-section">
        <div class="floors-header">
            <div>
                <h4><i class="fas fa-utensils"></i> Restaurante</h4>
                <small>Seleccione una mesa</small>
                @if($orderMode === 'print')
                <span class="badge badge-{{ $printServerRunning ? 'success' : 'danger' }}" id="printServerBadge" style="font-size:9px; margin-left:5px;">
                    <i class="fas fa-{{ $printServerRunning ? 'check-circle' : 'times-circle' }}"></i>
                    Print Server {{ $printServerRunning ? 'activo' : 'inactivo' }}
                </span>
                @endif
            </div>
            <span class="badge badge-{{ $orderMode === 'print' ? 'info' : 'secondary' }}" style="font-size:11px;">
                <i class="fas {{ $orderMode === 'print' ? 'fa-print' : 'fa-tv' }}"></i>
                {{ $orderMode === 'print' ? 'Impresión 80mm' : 'KDS' }}
            </span>
        </div>
        
        <div class="floors-tabs" id="floorsTabs">
            @foreach($floors as $floor)
            <button class="floor-tab {{ $loop->first ? 'active' : '' }}" data-floor-id="{{ $floor->id }}" onclick="selectFloor({{ $floor->id }})">
                {{ $floor->name }}
            </button>
            @endforeach
            @if($floors->isEmpty())
            <span class="text-muted">No hay pisos</span>
            @endif
        </div>
        
        <div class="tables-grid" id="tablesGrid">
            @foreach($floors as $floor)
                @foreach($floor->tables as $table)
                <div class="table-card {{ strtolower($table->status) }} {{ $table->activeOrder() ? 'has-order' : '' }}"
                     data-table-id="{{ $table->id }}"
                     data-floor-id="{{ $floor->id }}"
                     data-order-id="{{ $table->activeOrder()?->id }}"
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

{{-- Modal Confirmación --}}
<div class="qty-overlay" id="confirmOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:25px; border-radius:10px; min-width:350px; max-width:90%; text-align:center;">
        <div style="font-size:40px; margin-bottom:10px;" id="confirmIcon"><i class="fas fa-question-circle" style="color:#ffc107;"></i></div>
        <h5 style="margin:0 0 10px 0;" id="confirmTitle">Confirmar</h5>
        <p style="color:#666; margin-bottom:20px;" id="confirmMessage">¿Está seguro?</p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button type="button" class="btn btn-secondary" id="confirmCancelBtn" onclick="closeConfirm()">Cancelar</button>
            <button type="button" class="btn btn-primary" id="confirmOkBtn" onclick="confirmOk()">Aceptar</button>
        </div>
    </div>
</div>

{{-- Modal Toast --}}
<div id="toastAlert" style="display:none; position:fixed; top:20px; right:20px; z-index:99999; background:#28a745; color:white; padding:15px 25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); font-weight:bold; font-size:14px;">
    <i class="fas fa-check-circle mr-2"></i> <span id="toastMessage">Operación exitosa</span>
</div>

{{-- Modal Cantidad --}}
<div class="qty-overlay" id="qtyOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:300px; max-width:90%;">
        <h5 style="margin:0 0 15px 0;">Cantidad</h5>
        <input type="number" id="itemQtyInput" class="form-control" value="1" min="0.1" step="0.1" style="margin-bottom:10px;">
        <textarea id="itemNotesInput" class="form-control" rows="2" placeholder="Nota para cocina (opcional)..." style="margin-bottom:10px;"></textarea>
        <small class="text-muted d-block mb-2">Producto: <span id="modalProductName"></span></small>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeQtyModal()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="confirmAddItem()">Agregar</button>
        </div>
    </div>
</div>

{{-- Modal Editar Nota Item --}}
<div class="qty-overlay" id="itemNotesOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:300px; max-width:90%;">
        <h5 style="margin:0 0 15px 0;">Nota del Producto</h5>
        <input type="hidden" id="editItemNotesItemId">
        <textarea id="editItemNotesInput" class="form-control" rows="2" placeholder="Nota para cocina..." style="margin-bottom:10px;"></textarea>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeItemNotesModal()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="saveItemNotes()">Guardar</button>
        </div>
    </div>
</div>

{{-- Modal Contraseña Admin --}}
<div class="qty-overlay" id="adminPasswordOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:320px; max-width:90%;">
        <h5 style="margin:0 0 5px 0;">Autorización requerida</h5>
        <p style="font-size:13px; color:#666; margin-bottom:15px;">Ingrese su contraseña de administrador para eliminar este producto</p>
        <input type="hidden" id="adminPasswordItemId">
        <input type="password" id="adminPasswordInput" class="form-control" placeholder="Contraseña" style="margin-bottom:10px;" autocomplete="off">
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeAdminPasswordModal()">Cancelar</button>
            <button type="button" class="btn btn-danger" onclick="confirmAdminPassword()">Eliminar</button>
        </div>
    </div>
</div>

{{-- Table Order Modal --}}
<div class="table-order-modal" id="tableOrderModal">
    <div class="modal-header-bar">
        <div style="min-width:120px;">
            <h4 id="modalTableName">Mesa</h4>
            <small id="modalOrderNumber">Sin pedido</small>
        </div>
        <div style="flex:1; text-align:center; padding:0 10px;">
            <div style="display:inline-block; position:relative; max-width:320px; width:100%;">
                <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#999; font-size:12px;"></i>
                <input type="text" id="productSearch" placeholder="Buscar producto..." oninput="searchProducts(this.value)" style="width:100%; padding:4px 10px 4px 26px; border:none; border-radius:15px; font-size:12px; outline:none; box-sizing:border-box; background:rgba(255,255,255,0.9);">
            </div>
        </div>
        <button class="btn-close-modal" onclick="closeModal()">&#8592;</button>
    </div>
    
    <div class="modal-tabs">
        <div class="modal-tab active" data-tab="products" onclick="switchTab('products')">
            <i class="fas fa-box-open"></i> Productos
        </div>
        <div class="modal-tab" data-tab="order" onclick="switchTab('order')">
            <i class="fas fa-receipt"></i> Pedido
            <span class="badge badge-light" id="itemsCount" style="display:none;">0</span>
        </div>
    </div>
    
    <div class="modal-content-area">
        <div id="tabProducts">
            <div class="products-categories" id="productsCategories">
                <button class="category-btn active" data-category="all" onclick="filterProducts('all')">Todos</button>
                @foreach($categories as $category)
                <button class="category-btn" data-category="{{ $category->id }}" onclick="filterProducts({{ $category->id }})">
                    {{ $category->nombre }}
                </button>
                @endforeach
            </div>
            <div class="products-grid" id="productsList">
                @foreach($products as $product)
                <div class="product-card"
                     data-product-id="{{ $product->id }}"
                     data-category-id="{{ $product->category_id }}"
                     data-product-name="{{ $product->descripcion }}"
                     data-product-price="{{ $product->precio }}"
                     onclick="addProductToOrder({{ $product->id }})">
                    <div class="product-name">{{ $product->descripcion }}</div>
                    <div class="product-price">S/ {{ number_format($product->precio, 2) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        <div id="tabOrder" style="display: none;">
            <div id="orderItems">
                <div class="order-empty">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Seleccione productos</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="order-totals-box" id="orderTotals" style="display: none;">
        <div class="order-total-row"><span>Subtotal:</span><span id="orderSubtotal">S/ 0.00</span></div>
        <div class="order-total-row"><span>IGV ({{ $igvPercent ?? 18 }}%):</span><span id="orderIgv">S/ 0.00</span></div>
        <div class="order-total-row grand"><span>TOTAL:</span><span id="orderTotal">S/ 0.00</span></div>
    </div>
    
    <div class="modal-actions">
        @if($orderMode === 'print')
        <button class="btn-action btn-send" onclick="sendToKitchen()"><i class="fas fa-paper-plane"></i><br>Enviar</button>
        <button class="btn-action btn-prebill" onclick="showPrebillOptions(event)" id="btnPrebill" disabled><i class="fas fa-receipt"></i><br>Precuenta</button>
        @else
        <button class="btn-action btn-kitchen" onclick="sendToKitchen()"><i class="fas fa-paper-plane"></i><br>Cocina</button>
        <button class="btn-action btn-send" onclick="printKitchenTicket()"><i class="fas fa-paper-plane"></i><br>Enviar</button>
        <button class="btn-action btn-prebill" onclick="showPrebillOptions(event)" id="btnPrebill" disabled><i class="fas fa-receipt"></i><br>Precuenta</button>
        <button class="btn-action btn-close-order" onclick="closeTable()"><i class="fas fa-check"></i><br>Cerrar</button>
        @endif
        @if(!auth()->user()->isMozo())
        <button class="btn-action btn-charge" onclick="showChargeModal()" id="btnCharge" disabled><i class="fas fa-credit-card"></i><br>Cobrar</button>
        <button class="btn-action btn-cancel-order" onclick="cancelOrder()" id="btnCancelOrder" disabled><i class="fas fa-times"></i><br>Anular</button>
        @endif
        <button class="btn-action btn-move" onclick="showMoveTableModal()" id="btnMoveTable" disabled><i class="fas fa-arrows-alt"></i><br>Mover</button>
        @if(!auth()->user()->isMozo())
        <button class="btn-action btn-cash-drawer" onclick="openCashDrawer()" id="btnCashDrawer" disabled><i class="fas fa-cash-register"></i><br>Caja</button>
        @endif
    </div>
</div>

<div class="qty-overlay" id="prebillOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.3); z-index:99999; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:0; border-radius:10px; min-width:220px; max-width:90%; overflow:hidden;">
        <div style="padding:12px 15px; background:linear-gradient(135deg, #17a2b8, #138496); color:white; display:flex; justify-content:space-between; align-items:center;">
            <h5 style="margin:0; font-size:14px;"><i class="fas fa-receipt"></i> Precuenta</h5>
            <button onclick="closePrebillOptions()" style="background:none; border:none; color:white; font-size:20px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div style="padding:8px 0;">
            <div class="prebill-option" onclick="printPrebillTo('precuenta')"><i class="fas fa-print" style="width:20px; color:#17a2b8;"></i> Precuenta</div>
            <div class="prebill-option" onclick="printPrebillTo('precuenta2')"><i class="fas fa-print" style="width:20px; color:#17a2b8;"></i> Precuenta 2</div>
            <div class="prebill-option" onclick="printPrebillTo('precuenta3')"><i class="fas fa-print" style="width:20px; color:#17a2b8;"></i> Precuenta 3</div>
        </div>
    </div>
</div>

{{-- Customer Modal --}}
<div class="qty-overlay" id="customerModalOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10001; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:0; border-radius:10px; min-width:500px; max-width:90%; overflow:hidden;">
        <div style="padding:12px 15px; background:linear-gradient(135deg, #007bff, #0056b3); color:white; display:flex; justify-content:space-between; align-items:center;">
            <h5 style="margin:0; font-size:16px;"><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
            <button onclick="closeCustomerModal()" style="background:none; border:none; color:white; font-size:22px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div style="padding:0;"><iframe id="customerFrame" src="" style="width:100%; height:450px; border:none;"></iframe></div>
    </div>
</div>

{{-- Move Table Modal --}}
<div class="qty-overlay" id="moveTableOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:400px; max-width:500px; max-height:80vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h5 style="margin:0;"><i class="fas fa-arrows-alt"></i> Mover pedido a otra mesa</h5>
            <button onclick="closeMoveTableModal()" style="border:none; background:none; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <div id="moveTableList" style="max-height:55vh; overflow-y:auto;">
        </div>
        <div style="text-align:center; margin-top:15px;">
            <button class="btn btn-secondary" onclick="closeMoveTableModal()">Cancelar</button>
        </div>
    </div>
</div>

{{-- Charge Modal --}}
<div class="charge-overlay" id="chargeOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="charge-popup" style="background:white; padding:25px; border-radius:10px; min-width:400px; max-width:500px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h5 style="margin:0;"><i class="fas fa-credit-card"></i> Cobrar Pedido <span id="chargeOrderNumber" style="font-weight:normal; font-size:14px;"></span></h5>
            <button onclick="closeChargeModal()" style="border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div style="margin-bottom:12px;">
            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;"><i class="fas fa-user"></i> Cliente</label>
            <div style="display:flex; gap:5px;">
                <div style="flex:1; position:relative;">
                    <input type="text" id="chargeCustomerSearch" class="form-control form-control-sm" placeholder="Buscar cliente..." autocomplete="off">
                    <input type="hidden" id="chargeCustomerId" value="">
                    <div id="chargeCustomerDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ddd; border-radius:4px; max-height:200px; overflow-y:auto; z-index:999;"></div>
                </div>
                <button type="button" class="btn btn-sm btn-success" onclick="openCustomerModal()" title="Nuevo cliente"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div style="display:flex; gap:8px; margin-bottom:12px;">
            <div style="flex:1;"><label>Tipo Documento</label><select id="chargeDocumentType" class="form-control form-control-sm" onchange="updateChargeSerie()"><option value="03">BOLETA</option><option value="01">FACTURA</option><option value="NV" selected>NOTA DE VENTA</option></select></div>
            <div style="flex:1;"><label>Serie</label><input type="text" id="chargeSerieDisplay" class="form-control form-control-sm" readonly disabled></div>
        </div>
        <div style="margin-bottom:8px;">
            <label style="font-size:12px; font-weight:600; color:#444;">Referencia</label>
            <input type="text" id="chargeReference" class="form-control form-control-sm" placeholder="N° operación (opcional)">
        </div>
        <div style="margin-bottom:10px;">
            <label style="font-size:12px; font-weight:600; color:#444;">Métodos de Pago</label>
            <div id="paymentsContainer"></div>
            <button type="button" class="btn btn-link btn-sm" onclick="addPaymentRow()" style="padding:4px 0;">
                <i class="fas fa-plus"></i> Agregar otro método
            </button>
            <div style="display:flex; justify-content:flex-end; gap:15px; font-weight:bold; margin-top:6px; font-size:13px;">
                <span>Pendiente: S/ <span id="pendingAmount" style="color:#dc3545;">0.00</span></span>
                <span>Vuelto: S/ <span id="chargeVuelto" style="color:#28a745;">0.00</span></span>
            </div>
        </div>
        <div style="border-top:2px solid #eee; padding-top:12px; margin-bottom:15px;">
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;"><span>Subtotal:</span><span id="chargeSubtotal">S/ 0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;"><span>IGV ({{ $igvPercent ?? 18 }}%):</span><span id="chargeIgv">S/ 0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:bold; margin-top:8px;"><span>TOTAL:</span><span id="chargeTotal">S/ 0.00</span></div>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-secondary btn-sm" onclick="closeChargeModal()" style="flex:0 0 80px;">Cancelar</button>
            <button class="btn btn-success btn-sm" id="btnProcessCharge" onclick="processCharge()" style="flex:1; padding:8px 0;"><i class="fas fa-credit-card"></i> COBRAR S/ 0.00</button>
        </div>
    </div>
</div>

{{-- Modal Confirmación --}}
<div class="qty-overlay" id="confirmOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:25px; border-radius:10px; min-width:350px; max-width:90%; text-align:center;">
        <div style="font-size:40px; margin-bottom:10px;" id="confirmIcon"><i class="fas fa-question-circle" style="color:#ffc107;"></i></div>
        <h5 style="margin:0 0 10px 0;" id="confirmTitle">Confirmar</h5>
        <p style="color:#666; margin-bottom:20px;" id="confirmMessage">¿Está seguro?</p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button type="button" class="btn btn-secondary" id="confirmCancelBtn" onclick="closeConfirm()">Cancelar</button>
            <button type="button" class="btn btn-primary" id="confirmOkBtn" onclick="confirmOk()">Aceptar</button>
        </div>
    </div>
</div>

<div id="toastAlert" style="display:none; position:fixed; top:20px; right:20px; z-index:99999; background:#28a745; color:white; padding:15px 25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); font-weight:bold; font-size:14px;">
    <i class="fas fa-check-circle mr-2"></i> <span id="toastMessage">Operación exitosa</span>
</div>

{{-- Modal Cantidad --}}
<div class="qty-overlay" id="qtyOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:300px; max-width:90%;">
        <h5 style="margin:0 0 15px 0;">Cantidad</h5>
        <input type="number" id="itemQtyInput" class="form-control" value="1" min="0.1" step="0.1" style="margin-bottom:10px;">
        <textarea id="itemNotesInput" class="form-control" rows="2" placeholder="Nota para cocina (opcional)..." style="margin-bottom:10px;"></textarea>
        <small class="text-muted d-block mb-2">Producto: <span id="modalProductName"></span></small>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeQtyModal()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="confirmAddItem()">Agregar</button>
        </div>
    </div>
</div>

{{-- Modal Editar Nota Item --}}
<div class="qty-overlay" id="itemNotesOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:300px; max-width:90%;">
        <h5 style="margin:0 0 15px 0;">Nota del Producto</h5>
        <input type="hidden" id="editItemNotesItemId">
        <textarea id="editItemNotesInput" class="form-control" rows="2" placeholder="Nota para cocina..." style="margin-bottom:10px;"></textarea>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeItemNotesModal()">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="saveItemNotes()">Guardar</button>
        </div>
    </div>
</div>

{{-- Modal Contraseña Admin --}}
<div class="qty-overlay" id="adminPasswordOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:20px; border-radius:10px; min-width:320px; max-width:90%;">
        <h5 style="margin:0 0 5px 0;">Autorización requerida</h5>
        <p style="font-size:13px; color:#666; margin-bottom:15px;">Ingrese su contraseña de administrador para eliminar este producto</p>
        <input type="hidden" id="adminPasswordItemId">
        <input type="password" id="adminPasswordInput" class="form-control" placeholder="Contraseña" style="margin-bottom:10px;" autocomplete="off">
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeAdminPasswordModal()">Cancelar</button>
            <button type="button" class="btn btn-danger" onclick="confirmAdminPassword()">Eliminar</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentOrderId = null;
let currentTableId = null;
let currentTableName = null;
let orderModalOpen = false;
let productsData = @json($products);
let customersData = @json($customers);
let seriesData = @json($series);
let allFloors = @json($floors);
let igvPercent = {{ $igvPercent ?? 18 }};
let pendingProductId = null;
let previousTableBorderColor = {};
let currentFloorId = null;
let pendingDeleteItems = {};
let pendingDeleteItemId = null;
let pendingCancelAction = null;

// Initialize: show only first floor's tables
document.addEventListener('DOMContentLoaded', function() {
    const firstFloor = document.querySelector('.floor-tab');
    if (firstFloor) {
        const floorId = parseInt(firstFloor.dataset.floorId);
        selectFloor(floorId);
    }
    pollActiveOrders();
    setInterval(pollActiveOrders, 3000);
    
    const psBadge = document.getElementById('printServerBadge');
    if (psBadge) {
        setInterval(pollPrintServer, 10000);
    }
    
    const chargeSearch = document.getElementById('chargeCustomerSearch');
    if (chargeSearch) {
        chargeSearch.addEventListener('input', function(e) { searchChargeCustomers(e.target.value); });
        chargeSearch.addEventListener('blur', function() { setTimeout(function() { document.getElementById('chargeCustomerDropdown').style.display = 'none'; }, 200); });
    }
});

function selectFloor(floorId) {
    currentFloorId = floorId;
    
    document.querySelectorAll('.floor-tab').forEach(t => t.classList.remove('active'));
    const activeTab = document.querySelector(`.floor-tab[data-floor-id="${floorId}"]`);
    if (activeTab) activeTab.classList.add('active');
    
    document.querySelectorAll('.table-card').forEach(card => {
        const cardFloorId = parseInt(card.dataset.floorId);
        if (floorId === null || cardFloorId === floorId) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function selectTable(tableId) {
    orderModalOpen = true;
    const table = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
    if (!table) return;
    
    currentTableId = tableId;
    currentTableName = table.querySelector('.table-name').textContent;
    const orderId = table.dataset.orderId;
    
    document.getElementById('modalTableName').textContent = currentTableName;
    document.getElementById('modalOrderNumber').textContent = orderId ? 'Pedido: ' + orderId : 'Abriendo...';
    const cancelBtn = document.getElementById('btnCancelOrder');
    if (cancelBtn) cancelBtn.disabled = false;
    const moveBtn = document.getElementById('btnMoveTable');
    if (moveBtn) moveBtn.disabled = false;
    const drawerBtn = document.getElementById('btnCashDrawer');
    if (drawerBtn) drawerBtn.disabled = false;
    
    document.getElementById('tableOrderModal').classList.add('show');
    switchTab('products');
    
    if (orderId) {
        loadOrder(orderId);
    } else {
        openTable(tableId);
    }
}
    
function closeModal() {
    if (currentTableId && currentOrderId) {
        const card = document.querySelector(`.table-card[data-table-id="${currentTableId}"]`);
        if (card) card.dataset.orderId = currentOrderId;
    }
    orderModalOpen = false;
    document.getElementById('tableOrderModal').classList.remove('show');
    document.getElementById('chargeOverlay').style.display = 'none';
    document.getElementById('customerModalOverlay').style.display = 'none';
    document.getElementById('moveTableOverlay').style.display = 'none';
    document.getElementById('prebillOverlay').style.display = 'none';
    resetTableStyle(currentTableId);
}

function switchTab(tab) {
    document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.modal-tab[data-tab="${tab}"]`).classList.add('active');
    
    document.getElementById('tabProducts').style.display = tab === 'products' ? 'block' : 'none';
    document.getElementById('tabOrder').style.display = tab === 'order' ? 'block' : 'none';
    const totals = document.getElementById('orderTotals');
    if (totals) totals.style.display = (tab === 'order' && totals.dataset.hasItems === 'true') ? 'block' : 'none';
}

function resetTableStyle(tableId) {
    const table = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
    if (table && previousTableBorderColor[tableId]) {
        table.style.borderColor = previousTableBorderColor[tableId];
        table.style.borderWidth = '3px';
    }
}

function openTable(tableId) {
    document.getElementById('modalOrderNumber').textContent = 'Abriendo mesa...';
    
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
        if (data.success) {
            currentOrderId = data.order_id;
            document.querySelector(`.table-card[data-table-id="${tableId}"]`).dataset.orderId = data.order_id;
            document.getElementById('modalOrderNumber').textContent = 'Pedido: ' + (data.order_number || '#' + data.order_id);
            renderOrder({ items: [], subtotal: 0, igv: 0, total: 0 });
        } else {
            showError(data.message || 'Error al abrir mesa');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showError('Error al abrir mesa: ' + err.message);
    });
}

function loadOrder(orderId) {
    document.getElementById('modalOrderNumber').textContent = 'Cargando pedido...';
    
    fetch('/restaurant/orders/' + orderId, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            currentOrderId = orderId;
            const order = data.order;
            window.currentOrderData = order;
            document.getElementById('modalOrderNumber').textContent = 'Pedido: ' + order.order_number;
            renderOrder(order);
        } else {
            showError(data.message || 'Error al cargar pedido');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showError('Error al cargar pedido: ' + err.message);
    });
}

function renderOrder(order) {
    window.currentOrderData = order;
    const container = document.getElementById('orderItems');
    
    if (!order.items || order.items.length === 0) {
        container.innerHTML = '<div class="order-empty"><i class="fas fa-shopping-basket"></i><p>Seleccione productos</p></div>';
        document.getElementById('orderTotals').style.display = 'none';
        document.getElementById('orderTotals').dataset.hasItems = 'false';
        document.getElementById('itemsCount').style.display = 'none';
        const chargeBtn = document.getElementById('btnCharge');
        if (chargeBtn) chargeBtn.disabled = true;
        document.getElementById('btnPrebill').disabled = true;
        return;
    }
    
    const chargeBtn = document.getElementById('btnCharge');
    if (chargeBtn) chargeBtn.disabled = order.status === 'OPEN';
    document.getElementById('btnPrebill').disabled = false;
    
    let html = '';
    order.items.forEach(item => {
        const statusClass = item.kitchen_status.toLowerCase();
        const statusLabel = {
            'PENDING': 'Pendiente',
            'SENT': 'Enviado',
            'READY': 'Listo',
            'DELIVERED': 'Entregado'
        }[item.kitchen_status] || item.kitchen_status;
        
        html += `<div class="order-item-row">
            <div class="order-item-info">
                <div class="order-item-name">${item.product_name}</div>
                <div class="order-item-qty">${item.quantity} x S/ ${parseFloat(item.unit_price).toFixed(2)} = S/ ${parseFloat(item.total).toFixed(2)}</div>
                ${item.notes ? `<div class="order-item-note"><i class="fas fa-sticky-note"></i> ${item.notes}</div>` : ''}
                ${item.kitchen_status !== 'PENDING' ? `<span class="badge badge-${statusClass === 'sent' ? 'warning' : statusClass === 'ready' ? 'success' : 'info'}" style="font-size:10px;">${statusLabel}</span>` : ''}
            </div>
            <div class="order-item-actions">
                <button class="btn-qty-change" onclick="changeItemQty(${item.id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button class="btn-qty-change" onclick="changeItemQty(${item.id}, 1)">+</button>
                <button class="btn-note-item" onclick="editItemNotes(${item.id}, '${item.notes || ''}')" title="Agregar nota"><i class="fas fa-edit"></i></button>
                <button class="btn-remove-item" onclick="removeItem(${item.id})"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
    document.getElementById('orderSubtotal').textContent = 'S/ ' + parseFloat(order.subtotal).toFixed(2);
    document.getElementById('orderIgv').textContent = 'S/ ' + parseFloat(order.igv).toFixed(2);
    document.getElementById('orderTotal').textContent = 'S/ ' + parseFloat(order.total).toFixed(2);
    const isOrderTab = document.getElementById('tabOrder').style.display !== 'none';
    const totals = document.getElementById('orderTotals');
    totals.dataset.hasItems = 'true';
    totals.style.display = isOrderTab ? 'block' : 'none';
    document.getElementById('itemsCount').textContent = order.items.length;
    document.getElementById('itemsCount').style.display = 'inline';
}

let activeCategory = 'all';
window._searchQuery = '';

function filterProducts(categoryId) {
    activeCategory = categoryId;
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.category == categoryId);
    });
    applyFilters();
}

function searchProducts(query) {
    window._searchQuery = query.toLowerCase().trim();
    applyFilters();
}

function applyFilters() {
    const q = window._searchQuery || '';
    document.querySelectorAll('.product-card').forEach(card => {
        const catMatch = activeCategory === 'all' || card.dataset.categoryId == activeCategory;
        const nameMatch = !q || card.dataset.productName.toLowerCase().includes(q);
        card.style.display = (catMatch && nameMatch) ? '' : 'none';
    });
}

function addProductToOrder(productId) {
    pendingProductId = productId;
    const product = productsData.find(p => p.id === productId);
    document.getElementById('modalProductName').textContent = product.descripcion;
    document.getElementById('itemQtyInput').value = 1;
    document.getElementById('itemQtyInput').focus();
    document.getElementById('qtyOverlay').style.display = 'flex';
}

function closeQtyModal() {
    document.getElementById('qtyOverlay').style.display = 'none';
    document.getElementById('itemNotesInput').value = '';
}

function confirmAddItem() {
    const quantity = parseFloat(document.getElementById('itemQtyInput').value);
    if (!quantity || quantity <= 0) {
        showError('Ingrese una cantidad válida');
        return;
    }
    
    const itemNotes = document.getElementById('itemNotesInput').value.trim();
    document.getElementById('qtyOverlay').style.display = 'none';
    document.getElementById('itemNotesInput').value = '';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/restaurant/orders/' + currentOrderId + '/items', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: pendingProductId,
            quantity: quantity,
            notes: itemNotes || null
        })
    })
    .then(async res => {
        const text = await res.text();
        if (!res.ok) throw new Error('HTTP ' + res.status + ': ' + text);
        return JSON.parse(text);
    })
    .then(data => {
        if (data.success) {
            loadOrder(currentOrderId);
            switchTab('order');
        } else {
            showError(data.message || 'Error al agregar producto');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showError('Error al agregar producto: ' + err.message);
    });
}

function editItemNotes(itemId, currentNotes) {
    document.getElementById('editItemNotesItemId').value = itemId;
    document.getElementById('editItemNotesInput').value = currentNotes || '';
    document.getElementById('editItemNotesInput').focus();
    document.getElementById('itemNotesOverlay').style.display = 'flex';
}

function closeItemNotesModal() {
    document.getElementById('itemNotesOverlay').style.display = 'none';
}

function saveItemNotes() {
    const itemId = document.getElementById('editItemNotesItemId').value;
    const notes = document.getElementById('editItemNotesInput').value.trim();
    if (!itemId) return;
    closeItemNotesModal();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/restaurant/orders/items/' + itemId, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ notes: notes || null })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadOrder(currentOrderId);
        } else {
            showError(data.message || 'Error al guardar nota');
        }
    });
}

function changeItemQty(itemId, delta) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/restaurant/orders/items/' + itemId, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity_delta: delta })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadOrder(currentOrderId);
        } else {
            showError(data.message);
        }
    });
}

function removeItem(itemId) {
    const order = window.currentOrderData;
    const item = order ? order.items.find(i => i.id === itemId) : null;
    const needsAdmin = item && ['SENT', 'READY', 'DELIVERED'].includes(item.kitchen_status);
    
    if (needsAdmin) {
        if (pendingDeleteItems[itemId]) {
            pendingDeleteItemId = itemId;
            document.getElementById('adminPasswordItemId').value = itemId;
            document.getElementById('adminPasswordInput').value = '';
            document.getElementById('adminPasswordOverlay').style.display = 'flex';
            document.getElementById('adminPasswordInput').focus();
        } else {
            pendingDeleteItems[itemId] = true;
            showAlert('Este producto ya está enviado a cocina. Presione eliminar nuevamente para confirmar con contraseña de administrador.');
        }
        return;
    }
    
    showConfirm('¿Eliminar producto?', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('/restaurant/orders/items/' + itemId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadOrder(currentOrderId);
            } else {
                showError(data.message);
            }
        });
    });
}

let confirmCallback = null;

function showConfirm(msg, callback) {
    document.getElementById('confirmMessage').textContent = msg;
    document.getElementById('confirmTitle').textContent = 'Confirmar';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-question-circle" style="color:#ffc107;"></i>';
    document.getElementById('confirmCancelBtn').style.display = '';
    confirmCallback = callback;
    document.getElementById('confirmOverlay').style.display = 'flex';
}

function closeConfirm() {
    document.getElementById('confirmOverlay').style.display = 'none';
    confirmCallback = null;
}

function confirmOk() {
    document.getElementById('confirmOverlay').style.display = 'none';
    if (confirmCallback) {
        const cb = confirmCallback;
        confirmCallback = null;
        cb();
    }
}

function showAlert(msg) {
    document.getElementById('confirmMessage').textContent = msg;
    document.getElementById('confirmTitle').textContent = '';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-check-circle" style="color:#28a745;"></i>';
    document.getElementById('confirmCancelBtn').style.display = 'none';
    document.getElementById('confirmOverlay').style.display = 'flex';
    document.getElementById('confirmOkBtn').onclick = function() {
        document.getElementById('confirmOverlay').style.display = 'none';
        document.getElementById('confirmOkBtn').onclick = confirmOk;
    };
}

function showError(msg) {
    document.getElementById('confirmMessage').textContent = msg;
    document.getElementById('confirmTitle').textContent = '';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-exclamation-circle" style="color:#dc3545;"></i>';
    document.getElementById('confirmCancelBtn').style.display = 'none';
    document.getElementById('confirmOverlay').style.display = 'flex';
    document.getElementById('confirmOkBtn').onclick = function() {
        document.getElementById('confirmOverlay').style.display = 'none';
        document.getElementById('confirmOkBtn').onclick = confirmOk;
    };
}

function showToast(msg) {
    document.getElementById('toastMessage').textContent = msg;
    const toast = document.getElementById('toastAlert');
    toast.style.display = 'flex';
    setTimeout(function() { toast.style.display = 'none'; }, 3000);
}

function sendToKitchen() {
    if (!currentOrderId) return;
    showConfirm('¿Enviar pedido a cocina?', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch('/restaurant/orders/' + currentOrderId + '/send-to-kitchen', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Pedido enviado a cocina');
                closeModal();
            } else {
                showAlert(data.message || 'Error');
            }
        });
    });
}

function printKitchenTicket() {
    if (!currentOrderId) return;
    window.open('/restaurant/orders/' + currentOrderId + '/print-kitchen', '_blank');
}

function printPrebill() {
    if (!currentOrderId) return;
    window.open('/restaurant/orders/' + currentOrderId + '/print-prebill', '_blank', 'width=400,height=600');
}

function showPrebillOptions(event) {
    if (!currentOrderId) return;
    document.getElementById('prebillOverlay').style.display = 'flex';
}

function closePrebillOptions() {
    document.getElementById('prebillOverlay').style.display = 'none';
}

function printPrebillTo(printerKey) {
    closePrebillOptions();
    if (!currentOrderId) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/restaurant/orders/' + currentOrderId + '/print-prebill/' + printerKey, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    }).catch(function() {});
}

function showMoveTableModal() {
    if (!currentOrderId) return;
    var container = document.getElementById('moveTableList');
    var html = '';
    var currentTableIdNum = parseInt(currentTableId);
    
    allFloors.forEach(function(floor) {
        var hasTables = floor.tables && floor.tables.some(function(t) { return t.id !== currentTableIdNum; });
        if (!hasTables) return;
        
        html += '<div style="margin-bottom:12px;">';
        html += '<div style="font-weight:600; font-size:14px; color:#555; margin-bottom:6px; padding:4px 0; border-bottom:1px solid #eee;">' + floor.name + '</div>';
        
        floor.tables.forEach(function(t) {
            if (t.id === currentTableIdNum) return;
            var isOccupied = t.status === 'OCCUPIED';
            var isAvailable = t.status === 'AVAILABLE';
            var cssClass = isOccupied ? 'occupied' : (isAvailable ? 'available' : '');
            var icon = isOccupied ? 'fa-chair text-warning' : 'fa-chair text-success';
            var label = isOccupied ? 'Ocupada' : 'Disponible';
            
            html += '<div class="move-table-item ' + cssClass + '" onclick="selectMoveTable(' + t.id + ')" data-table-id="' + t.id + '">';
            html += '<div class="table-icon"><i class="fas ' + icon + '"></i></div>';
            html += '<div style="flex:1;"><strong>' + t.name + '</strong><br><small style="color:#888;">' + label + '</small></div>';
            html += '</div>';
        });
        
        html += '</div>';
    });
    
    container.innerHTML = html || '<p style="text-align:center;color:#888;">No hay otras mesas disponibles</p>';
    document.getElementById('moveTableOverlay').style.display = 'flex';
}

function closeMoveTableModal() {
    document.getElementById('moveTableOverlay').style.display = 'none';
}

function selectMoveTable(targetTableId) {
    closeMoveTableModal();
    showConfirm('¿Mover pedido a la mesa seleccionada?', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch('/restaurant/orders/' + currentOrderId + '/move-table', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ table_id: targetTableId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Pedido movido');
                location.reload();
            } else {
                showError(data.message || 'Error al mover pedido');
            }
        })
        .catch(function() {
            showError('Error de conexión');
        });
    });
}

function openCashDrawer() {
    fetch('/pos/open-drawer', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(config) {
        if (!config.success) {
            showError(config.message || 'Error');
            return;
        }
        var body = 'mode=escpos&data=' + encodeURIComponent(config.data);
        if (config.printer) body += '&printer=' + encodeURIComponent(config.printer);
        else if (config.ip) { body += '&ip=' + config.ip + '&port=' + config.port; }
        fetch('http://localhost:9100/print', {
            method: 'POST',
            mode: 'no-cors',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .catch(function() {});
        showToast('Cajón abierto');
    })
    .catch(function() {
        showError('Error al obtener configuración');
    });
}

function closeTable() {
    if (!currentOrderId) return;
    showConfirm('¿Cerrar pedido?', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('/restaurant/orders/' + currentOrderId + '/close', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Pedido cerrado');
                location.reload();
            } else {
                showError(data.message || 'Error');
            }
        });
    });
}

function cancelOrder() {
    if (!currentOrderId) return;
    
    const order = window.currentOrderData;
    const hasKitchenItems = order && order.items && order.items.some(i => ['SENT', 'READY', 'DELIVERED'].includes(i.kitchen_status));
    
    if (hasKitchenItems) {
        if (pendingCancelAction) {
            pendingCancelAction = currentOrderId;
            document.getElementById('adminPasswordItemId').value = 'cancel_' + currentOrderId;
            document.getElementById('adminPasswordInput').value = '';
            document.getElementById('adminPasswordOverlay').style.display = 'flex';
            document.getElementById('adminPasswordInput').focus();
        } else {
            pendingCancelAction = true;
            showAlert('El pedido tiene productos en cocina. Presione Anular nuevamente para confirmar con contraseña de administrador.');
        }
        return;
    }
    
    showConfirm('¿Anular pedido?', cancelOrderRequest);
}

function cancelOrderRequest(password) {
    const body = password ? { admin_password: password } : {};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/restaurant/orders/' + currentOrderId + '/cancel', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(body)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Pedido anulado');
            const tableCard = document.querySelector(`.table-card[data-table-id="${currentTableId}"]`);
            if (tableCard) {
                tableCard.className = 'table-card available';
                tableCard.dataset.orderId = '';
                const orderDiv = tableCard.querySelector('.table-order');
                if (orderDiv) orderDiv.remove();
            }
            location.reload();
        } else {
            showError(data.message || 'Error');
        }
    });
}

function closeAdminPasswordModal() {
    document.getElementById('adminPasswordOverlay').style.display = 'none';
    pendingCancelAction = null;
}

function confirmAdminPassword() {
    const itemId = document.getElementById('adminPasswordItemId').value;
    const password = document.getElementById('adminPasswordInput').value;
    if (!password) { showError('Ingrese su contraseña'); return; }
    
    closeAdminPasswordModal();
    
    if (itemId.startsWith('cancel_')) {
        cancelOrderRequest(password);
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('/restaurant/orders/items/' + itemId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ admin_password: password })
    })
    .then(res => res.json())
    .then(data => {
        delete pendingDeleteItems[itemId];
        if (data.success) {
            loadOrder(currentOrderId);
        } else {
            showError(data.message || 'Error');
        }
    });
}

function pollActiveOrders() {
    fetch('/restaurant/active-orders?_=' + Date.now(), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;
        const tablesWithOrders = {};
        data.orders.forEach(order => {
            tablesWithOrders[order.table_id] = order;
            const card = document.querySelector(`.table-card[data-table-id="${order.table_id}"]`);
            if (!card) return;
            card.className = 'table-card occupied has-order';
            if (!orderModalOpen) {
                card.dataset.orderId = order.id;
            }
            let orderDiv = card.querySelector('.table-order');
            if (!orderDiv) {
                orderDiv = document.createElement('div');
                orderDiv.className = 'table-order';
                card.appendChild(orderDiv);
            }
            orderDiv.textContent = order.order_number;
        });
        document.querySelectorAll('.table-card').forEach(card => {
            const tid = parseInt(card.dataset.tableId);
            if (!tid) return;
            if (!tablesWithOrders[tid] && card.classList.contains('occupied')) {
                card.className = 'table-card available';
                card.dataset.orderId = '';
                const orderDiv = card.querySelector('.table-order');
                if (orderDiv) orderDiv.remove();
            }
        });
    })
    .catch(() => {});
}

function pollPrintServer() {
    fetch('/restaurant/print-status?_=' + Date.now(), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        const badge = document.getElementById('printServerBadge');
        if (!badge) return;
        if (data.running) {
            badge.className = 'badge badge-success';
            badge.innerHTML = '<i class="fas fa-check-circle"></i> Print Server activo';
        } else {
            badge.className = 'badge badge-danger';
            badge.innerHTML = '<i class="fas fa-times-circle"></i> Print Server inactivo';
        }
    })
    .catch(() => {
        const badge = document.getElementById('printServerBadge');
        if (badge) {
            badge.className = 'badge badge-danger';
            badge.innerHTML = '<i class="fas fa-times-circle"></i> Print Server inactivo';
        }
    });
}

function showChargeModal() {
    if (!window.currentOrderData) return;
    const order = window.currentOrderData;
    if (order.status === 'OPEN') { showError('Debe enviar el pedido a cocina antes de cobrar'); return; }
    const total = parseFloat(order.total) || 0;
    document.getElementById('chargeOrderNumber').textContent = '#' + (order.order_number || order.id);
    document.getElementById('chargeSubtotal').textContent = 'S/ ' + (parseFloat(order.subtotal) || total / (1 + igvPercent / 100)).toFixed(2);
    document.getElementById('chargeIgv').textContent = 'S/ ' + (parseFloat(order.igv) || total - total / (1 + igvPercent / 100)).toFixed(2);
    document.getElementById('chargeTotal').textContent = 'S/ ' + total.toFixed(2);
    document.getElementById('paymentsContainer').innerHTML = '';
    addPaymentRow(total);
    var defaultCustomer = customersData.find(function(c) { return c.documento_numero === '88888888'; });
    if (defaultCustomer) {
        document.getElementById('chargeCustomerId').value = defaultCustomer.id;
        document.getElementById('chargeCustomerSearch').value = defaultCustomer.nombre;
    }
    document.getElementById('chargeOverlay').style.display = 'flex';
    updateChargeSerie();
}

function addPaymentRow(amount) {
    const container = document.getElementById('paymentsContainer');
    const row = document.createElement('div');
    row.className = 'payment-row';
    row.style.cssText = 'display:flex; gap:6px; margin-bottom:5px; align-items:center;';
    row.innerHTML = `
        <select class="payment-method form-control form-control-sm" style="flex:1;">
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA">TARJETA</option>
            <option value="YAPE">YAPE</option>
            <option value="PLIN">PLIN</option>
            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
        </select>
        <input type="number" class="payment-amount form-control form-control-sm" style="flex:1;" step="0.01" min="0" placeholder="Monto" oninput="updatePaymentSummary()">
        <button type="button" class="btn btn-danger btn-sm" onclick="removePaymentRow(this)" style="width:30px;height:30px;padding:0;">×</button>
    `;
    if (amount && amount > 0) {
        row.querySelector('.payment-amount').value = parseFloat(amount).toFixed(2);
    }
    container.appendChild(row);
    updatePaymentSummary();
}

function removePaymentRow(btn) {
    if (document.querySelectorAll('.payment-row').length <= 1) return;
    btn.closest('.payment-row').remove();
    updatePaymentSummary();
}

function updatePaymentSummary() {
    const total = parseFloat(document.getElementById('chargeTotal').textContent.replace('S/ ', '')) || 0;
    const amounts = document.querySelectorAll('.payment-amount');
    let sum = 0;
    amounts.forEach(function(inp) { sum += parseFloat(inp.value) || 0; });
    const pending = Math.max(0, total - sum);
    const vuelto = Math.max(0, sum - total);
    document.getElementById('pendingAmount').textContent = pending.toFixed(2);
    document.getElementById('chargeVuelto').textContent = vuelto.toFixed(2);
    const btn = document.getElementById('btnProcessCharge');
    if (pending === 0 && sum > 0) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-credit-card"></i> COBRAR S/ ' + total.toFixed(2);
    } else if (sum === 0) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-credit-card"></i> COBRAR S/ ' + total.toFixed(2);
    } else {
        btn.disabled = true;
        btn.innerHTML = 'Faltan S/ ' + pending.toFixed(2);
    }
}

function closeChargeModal() {
    document.getElementById('chargeOverlay').style.display = 'none';
}

function searchChargeCustomers(term) {
    if (term.length < 2) { document.getElementById('chargeCustomerDropdown').style.display = 'none'; return; }
    const termLower = term.toLowerCase();
    const results = customersData.filter(c => {
        return (c.nombre && c.nombre.toLowerCase().includes(termLower)) || (c.documento_numero && c.documento_numero.includes(term));
    });
    if (results.length === 0) {
        document.getElementById('chargeCustomerDropdown').innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
        document.getElementById('chargeCustomerDropdown').style.display = 'block';
        return;
    }
    let html = '';
    results.slice(0, 10).forEach(customer => {
        html += '<div class="customer-option" onclick="selectChargeCustomer(' + customer.id + ', \'' + customer.nombre.replace(/'/g, "\\'") + '\')">' +
            '<div class="customer-option-name">' + customer.nombre + '</div>' +
            '<div class="customer-option-doc">' + (customer.documento_tipo || '') + ': ' + (customer.documento_numero || '') + '</div></div>';
    });
    document.getElementById('chargeCustomerDropdown').innerHTML = html;
    document.getElementById('chargeCustomerDropdown').style.display = 'block';
}

function selectChargeCustomer(id, nombre) {
    document.getElementById('chargeCustomerId').value = id;
    document.getElementById('chargeCustomerSearch').value = nombre;
    document.getElementById('chargeCustomerDropdown').style.display = 'none';
}

function updateChargeSerie() {
    const docType = document.getElementById('chargeDocumentType').value;
    const typePrefixes = { '01': 'F', '03': 'B', 'NV': 'NV' };
    const prefix = typePrefixes[docType] || 'F';
    let defaultSerie = prefix + '001';
    if (seriesData && seriesData.length > 0) {
        const matchingSerie = seriesData.find(s => s.tipo_documento === docType);
        if (matchingSerie && matchingSerie.serie) defaultSerie = matchingSerie.serie;
    }
    document.getElementById('chargeSerieDisplay').value = defaultSerie;
}

function openCustomerModal() {
    document.getElementById('chargeOverlay').style.display = 'none';
    const companyId = {{ $companyId }};
    document.getElementById('customerFrame').src = '/customers/create?company_id=' + companyId + '&modal=1';
    document.getElementById('customerModalOverlay').style.display = 'flex';
}

function closeCustomerModal() {
    document.getElementById('customerModalOverlay').style.display = 'none';
    document.getElementById('chargeOverlay').style.display = 'flex';
}

function onCustomerCreated(customer) {
    customersData.push(customer);
    closeCustomerModal();
    selectChargeCustomer(customer.id, customer.nombre);
}

function processCharge() {
    if (!currentOrderId) return;
    const btn = document.getElementById('btnProcessCharge');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    fetch('/restaurant/orders/' + currentOrderId + '/charge', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            customer_id: document.getElementById('chargeCustomerId').value,
            document_type: document.getElementById('chargeDocumentType').value,
            payments: (function() {
                var rows = document.querySelectorAll('.payment-row');
                var arr = [];
                rows.forEach(function(r) {
                    var m = r.querySelector('.payment-method').value;
                    var a = parseFloat(r.querySelector('.payment-amount').value) || 0;
                    if (a > 0) arr.push({ method: m, amount: a });
                });
                return arr;
            })(),
            reference: document.getElementById('chargeReference').value,
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            closeChargeModal();
            document.getElementById('chargeCustomerSearch').value = '';
            document.getElementById('chargeCustomerId').value = '';
            var invoiceId = data.invoice_id;
            var vuelto = data.vuelto || 0;
            var msg = 'Total: S/ ' + data.total.toFixed(2);
            if (vuelto > 0) msg += '\nVuelto: S/ ' + vuelto.toFixed(2);
            showConfirm(msg + '\n\n¿Desea imprimir el comprobante?', function() {
                window.open('/pos/print/' + invoiceId + '/80mm', '_blank', 'width=400,height=600');
                location.reload();
            });
            document.getElementById('confirmCancelBtn').onclick = function() {
                document.getElementById('confirmOverlay').style.display = 'none';
                confirmCallback = null;
                location.reload();
            };
        } else {
            btn.innerHTML = '<i class="fas fa-credit-card"></i> COBRAR';
            showError(data.message || 'Error al procesar');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-credit-card"></i> COBRAR';
        showError('Error: ' + err.message);
    });
}
</script>
@endpush