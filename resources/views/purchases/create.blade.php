@extends('layouts.admin')
@section('title', 'Nueva Compra')
@section('page_title', 'Nueva Compra')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Compra</h3>
    </div>
    <form method="POST" action="{{ route('purchases.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo Documento</label>
                        <select name="tipo_documento" class="form-control" required>
                            <option value="FACTURA">Factura</option>
                            <option value="BOLETA">Boleta</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número Documento</label>
                        <input type="text" name="numero_documento" class="form-control" required placeholder="F001-00001">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Proveedor (opcional)</label>
                <select name="supplier_id" class="form-control">
                    <option value="">Sin proveedor</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }} - {{ $s->ruc }}</option>
                    @endforeach
                </select>
            </div>
            
            <hr>
            <h5>Productos</h5>
            <div class="row align-items-end mb-3">
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label>Producto</label>
                        <select id="productSelect" class="form-control">
                            <option value="">Seleccionar...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->precio }}" data-stock="{{ $p->stock }}">{{ $p->codigo }} - {{ $p->descripcion }} (Stock: {{ $p->stock }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Cantidad</label>
                        <input type="number" id="itemQty" class="form-control" value="1" min="1">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label>Precio Unit.</label>
                        <input type="number" id="itemPrice" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success" onclick="addItem()"><i class="fas fa-plus"></i> Agregar</button>
                </div>
            </div>
            
            <table class="table table-bordered mt-3">
                <thead class="thead-dark">
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="purchase-items"></tbody>
            </table>
            
            <div class="row justify-content-end">
                <div class="col-md-3">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-right"><strong>Total:</strong></td>
                            <td class="text-right" id="purchase-total">0.00</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('purchases.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="saveBtn" disabled>Guardar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let items = [];

document.getElementById('productSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('itemPrice').value = opt.dataset.price || 0;
});

function addItem() {
    const select = document.getElementById('productSelect');
    const opt = select.options[select.selectedIndex];
    if (!opt.value) { alert('Seleccione un producto'); return; }
    
    const qty = parseInt(document.getElementById('itemQty').value) || 0;
    const price = parseFloat(document.getElementById('itemPrice').value) || 0;
    if (qty <= 0 || price < 0) { alert('Ingrese cantidad y precio válidos'); return; }
    
    items.push({
        product_id: opt.value,
        nombre: opt.text,
        cantidad: qty,
        precio: price
    });
    
    renderItems();
    select.value = '';
    document.getElementById('itemQty').value = 1;
    document.getElementById('itemPrice').value = '';
}

function removeItem(idx) {
    items.splice(idx, 1);
    renderItems();
}

function renderItems() {
    const tbody = document.getElementById('purchase-items');
    tbody.innerHTML = '';
    let total = 0;
    
    items.forEach((item, idx) => {
        const subtotal = item.cantidad * item.precio;
        total += subtotal;
        const row = document.createElement('tr');
        row.innerHTML = '<td>' + item.nombre + '</td><td class="text-right">' + item.cantidad + '</td><td class="text-right">' + item.precio.toFixed(2) + '</td><td class="text-right">' + subtotal.toFixed(2) + '</td><td><button type="button" onclick="removeItem(' + idx + ')" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td><input type="hidden" name="items[' + idx + '][product_id]" value="' + item.product_id + '"><input type="hidden" name="items[' + idx + '][cantidad]" value="' + item.cantidad + '"><input type="hidden" name="items[' + idx + '][precio]" value="' + item.precio + '">';
        tbody.appendChild(row);
    });
    
    document.getElementById('purchase-total').textContent = total.toFixed(2);
    document.getElementById('saveBtn').disabled = items.length === 0;
}
</script>
@endpush