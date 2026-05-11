@extends('layouts.admin')
@section('title', 'Punto de Venta')
@section('page_title', 'Punto de Venta')

@section('css')
<style>
    body {
        overflow: hidden;
    }
    .main-footer {
        display: none;
    }
    .pos-container {
        display: flex;
        height: calc(100vh - 100px);
        gap: 10px;
        padding: 10px;
    }
    .categories-panel {
        width: 15%;
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        overflow-y: auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .products-panel {
        width: 55%;
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        overflow-y: auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .sale-panel {
        width: 30%;
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .category-btn {
        width: 100%;
        padding: 15px 10px;
        margin-bottom: 8px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
        font-size: 14px;
    }
    .category-btn:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .category-btn.active {
        box-shadow: 0 0 0 3px #333;
    }
    .products-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .product-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        border: 2px solid transparent;
    }
    .product-card:hover {
        border-color: #007bff;
        transform: scale(1.02);
    }
    .product-name {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .product-price {
        font-size: 14px;
        color: #28a745;
        font-weight: bold;
    }
    .sale-items {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 10px;
    }
    .sale-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    .sale-item-info {
        flex: 1;
    }
    .sale-item-name {
        font-weight: bold;
        font-size: 13px;
    }
    .sale-item-price {
        font-size: 12px;
        color: #666;
    }
    .sale-item-actions {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .qty-btn {
        width: 25px;
        height: 25px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .qty-minus {
        background: #dc3545;
        color: white;
    }
    .qty-plus {
        background: #28a745;
        color: white;
    }
    .sale-item-qty {
        font-weight: bold;
        min-width: 30px;
        text-align: center;
    }
    .sale-totals {
        border-top: 2px solid #007bff;
        padding-top: 10px;
        margin-bottom: 10px;
    }
    .sale-total-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 14px;
    }
    .sale-total-row.grand-total {
        font-size: 18px;
        font-weight: bold;
        color: #007bff;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        margin-top: 5px;
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
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    }
    .btn-pay:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .customer-select {
        margin-bottom: 10px;
    }
    .sale-header {
        font-weight: bold;
        font-size: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
        margin-bottom: 10px;
    }
    .empty-sale {
        text-align: center;
        color: #999;
        padding: 50px 20px;
    }
    .empty-sale i {
        font-size: 50px;
        margin-bottom: 10px;
    }
    .btn-cancel-sale {
        background: #dc3545;
        color: white;
        border: none;
        padding: 10px;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="pos-container">
    <div class="categories-panel">
        <h5 class="mb-3"><i class="fas fa-th-large"></i> Categorías</h5>
        <button class="category-btn active" style="background: #6f42c1;" onclick="filterByCategory(null)">
            <i class="fas fa-star"></i> Todos
        </button>
        @foreach($categories as $category)
        <button class="category-btn" style="background: {{ $category->color ?? '#007bff' }};" 
                onclick="filterByCategory({{ $category->id }})"
                id="cat-{{ $category->id }}">
            <i class="{{ $category->icon ?? 'fas fa-tag' }}"></i> {{ $category->nombre }}
        </button>
        @endforeach
    </div>
    
    <div class="products-panel">
        <h5 class="mb-3"><i class="fas fa-box"></i> Productos</h5>
        <div class="products-grid" id="productsGrid">
            @foreach($products as $product)
            <div class="product-card" onclick="addToSale({{ $product->id }})" 
                 data-category="{{ $product->category_id }}"
                 data-name="{{ $product->descripcion }}"
                 data-price="{{ $product->precio }}"
                 data-stock="{{ $product->stock }}"
                 id="product-{{ $product->id }}">
                <div class="product-name">{{ $product->descripcion }}</div>
                <div class="product-price">S/ {{ number_format($product->precio, 2) }}</div>
                <small class="text-muted">Stock: {{ $product->stock }}</small>
            </div>
            @endforeach
        </div>
    </div>
    
    <div class="sale-panel">
        <div class="sale-header">
            <i class="fas fa-shopping-cart"></i> Venta Actual
        </div>
        
        <div class="customer-select">
            <select id="customerSelect" class="form-control form-control-sm">
                <option value="">-- Cliente Varios --</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->nombre }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="sale-items" id="saleItems">
            <div class="empty-sale">
                <i class="fas fa-shopping-basket"></i>
                <p>No hay productos en la venta</p>
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
        
        <button class="btn-cancel-sale" onclick="cancelSale()">
            <i class="fas fa-trash"></i> Cancelar Venta
        </button>
        
        <button class="btn-pay" id="btnPay" onclick="processSale()" disabled>
            <i class="fas fa-credit-card"></i> COBRAR
        </button>
    </div>
</div>

<form id="saleForm" method="POST" action="{{ route('pos.store') }}" style="display: none;">
    @csrf
    <input type="hidden" name="customer_id" id="customerId">
    <input type="hidden" name="items_json" id="itemsJson">
    <input type="hidden" name="total" id="totalInput">
</form>

<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Venta Procesada</h5>
            </div>
            <div class="modal-body text-center">
                <h3>¡Venta registrada exitosamente!</h3>
                <p id="invoiceNumber"></p>
                <h4>Total: <span id="saleTotal"></span></h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeSuccessModal()">
                    <i class="fas fa-check"></i> Aceptar
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
@endsection

@section('js')
<script>
let saleItems = [];
let currentCategory = null;

function filterByCategory(categoryId) {
    currentCategory = categoryId;
    
    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
    if (categoryId === null) {
        document.querySelector('.category-btn').classList.add('active');
    } else {
        document.getElementById('cat-' + categoryId).classList.add('active');
    }
    
    document.querySelectorAll('.product-card').forEach(card => {
        if (categoryId === null || card.dataset.category == categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function addToSale(productId) {
    const productCard = document.getElementById('product-' + productId);
    const name = productCard.dataset.name;
    const price = parseFloat(productCard.dataset.price);
    const stock = parseInt(productCard.dataset.stock);
    
    const existingItem = saleItems.find(item => item.id === productId);
    if (existingItem) {
        if (existingItem.quantity < stock) {
            existingItem.quantity++;
        } else {
            showError('Stock insuficiente');
            return;
        }
    } else {
        if (stock <= 0) {
            showError('Producto sin stock');
            return;
        }
        saleItems.push({
            id: productId,
            name: name,
            price: price,
            quantity: 1,
            stock: stock
        });
    }
    
    renderSaleItems();
}

function removeFromSale(productId) {
    const existingItem = saleItems.find(item => item.id === productId);
    if (existingItem) {
        if (existingItem.quantity > 1) {
            existingItem.quantity--;
        } else {
            saleItems = saleItems.filter(item => item.id !== productId);
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

function cancelSale() {
    if (saleItems.length > 0) {
        if (confirm('¿Está seguro de cancelar la venta?')) {
            saleItems = [];
            renderSaleItems();
        }
    }
}

function renderSaleItems() {
    const container = document.getElementById('saleItems');
    
    if (saleItems.length === 0) {
        container.innerHTML = `
            <div class="empty-sale">
                <i class="fas fa-shopping-basket"></i>
                <p>No hay productos en la venta</p>
            </div>
        `;
        document.getElementById('btnPay').disabled = true;
        return;
    }
    
    let html = '';
    saleItems.forEach(item => {
        html += `
            <div class="sale-item">
                <div class="sale-item-info">
                    <div class="sale-item-name">${item.name}</div>
                    <div class="sale-item-price">S/ ${item.price.toFixed(2)} x ${item.quantity}</div>
                </div>
                <div class="sale-item-actions">
                    <button class="qty-btn qty-minus" onclick="decreaseQty(${item.id})">-</button>
                    <span class="sale-item-qty">${item.quantity}</span>
                    <button class="qty-btn qty-plus" onclick="increaseQty(${item.id})">+</button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    document.getElementById('btnPay').disabled = false;
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    saleItems.forEach(item => {
        subtotal += item.price * item.quantity;
    });
    
    const igv = subtotal * 0.18;
    const total = subtotal + igv;
    
    document.getElementById('subtotal').textContent = 'S/ ' + subtotal.toFixed(2);
    document.getElementById('igv').textContent = 'S/ ' + igv.toFixed(2);
    document.getElementById('total').textContent = 'S/ ' + total.toFixed(2);
}

function processSale() {
    if (saleItems.length === 0) {
        showError('No hay productos en la venta');
        return;
    }
    
    document.getElementById('customerId').value = document.getElementById('customerSelect').value;
    document.getElementById('itemsJson').value = JSON.stringify(saleItems);
    document.getElementById('totalInput').value = calculateTotal();
    
    document.getElementById('saleForm').submit();
}

function calculateTotal() {
    let subtotal = 0;
    saleItems.forEach(item => {
        subtotal += item.price * item.quantity;
    });
    return subtotal + (subtotal * 0.18);
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    $('#errorModal').modal('show');
}

function closeSuccessModal() {
    $('#successModal').modal('hide');
    saleItems = [];
    renderSaleItems();
    document.getElementById('customerSelect').value = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cancelSale();
    }
});
</script>
@endsection