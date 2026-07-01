@extends('layouts.admin')
@section('title', 'Pedidos Kiosko Pendientes')
@section('page_title', 'Pedidos Kiosko Pendientes')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Pedidos Pendientes de Pago</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>N° Pedido</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>
                        @foreach($order->items as $item)
                        <div>{{ $item->quantity }}x {{ $item->product_name }} - S/ {{ number_format($item->total, 2) }}</div>
                        @endforeach
                    </td>
                    <td><strong class="text-success">S/ {{ number_format($order->total, 2) }}</strong></td>
                    <td>{{ $order->created_at->format('d/m H:i') }}</td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="chargeKioskOrder({{ $order->id }}, {{ $order->total }})">
                            <i class="fas fa-cash-register"></i> Cobrar
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">No hay pedidos kiosko pendientes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Datos para el modal de cobro (reutiliza funciones existentes del restaurante)
let currentChargeOrderId = null;
let customersData = @json(\App\Models\Customer::where('company_id', $companyId ?? 1)->get());
let seriesData = @json(\App\Models\Serie::where('estado', 'ACTIVO')->get());
let igvPercent = {{ \App\Models\Company::find($companyId ?? 1)?->getActiveIgvPercent() ?? 18 }};

function chargeKioskOrder(orderId, total) {
    currentChargeOrderId = orderId;
    
    // Set modal data
    document.getElementById('chargeOrderNumber').textContent = '#' + orderId;
    const subtotal = total / (1 + igvPercent / 100);
    document.getElementById('chargeSubtotal').textContent = 'S/ ' + subtotal.toFixed(2);
    document.getElementById('chargeIgv').textContent = 'S/ ' + (total - subtotal).toFixed(2);
    document.getElementById('chargeTotal').textContent = 'S/ ' + total.toFixed(2);
    
    // Reset payments
    const container = document.getElementById('paymentsContainer');
    container.innerHTML = '';
    addPaymentRow(total);
    
    // Select default customer
    var defaultCustomer = customersData.find(function(c) { return c.documento_numero === '88888888'; });
    if (defaultCustomer) {
        document.getElementById('chargeCustomerId').value = defaultCustomer.id;
        document.getElementById('chargeCustomerSearch').value = defaultCustomer.nombre;
    }
    
    document.getElementById('chargeOverlay').style.display = 'flex';
    updateChargeSerie();
}

function processCharge() {
    if (!currentChargeOrderId) return;
    const btn = document.getElementById('btnProcessCharge');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const payments = [];
    document.querySelectorAll('.payment-row').forEach(function(row) {
        var m = row.querySelector('.payment-method').value;
        var a = parseFloat(row.querySelector('.payment-amount').value) || 0;
        if (a > 0) payments.push({ method: m, amount: a });
    });

    fetch('/restaurant/kiosk-charge/' + currentChargeOrderId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            customer_id: document.getElementById('chargeCustomerId').value,
            document_type: document.getElementById('chargeDocumentType').value,
            payments: payments,
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            closeChargeModal();
            var vuelto = data.vuelto || 0;
            var msg = 'Cobrado: S/ ' + data.total.toFixed(2);
            if (vuelto > 0) msg += '\nVuelto: S/ ' + vuelto.toFixed(2);
            alert(msg);
            location.reload();
        } else {
            btn.innerHTML = '<i class="fas fa-credit-card"></i> COBRAR';
            alert(data.message || 'Error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-credit-card"></i> COBRAR';
        alert('Error: ' + err.message);
    });
}

// Reuse charge modal functions from restaurant view
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
        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'.payment-row\').remove();updatePaymentSummary();" style="width:30px;height:30px;padding:0;">×</button>
    `;
    if (amount && amount > 0) row.querySelector('.payment-amount').value = parseFloat(amount).toFixed(2);
    container.appendChild(row);
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
    btn.disabled = !(pending === 0 && sum > 0);
    btn.innerHTML = pending === 0 && sum > 0
        ? '<i class="fas fa-credit-card"></i> COBRAR S/ ' + total.toFixed(2)
        : sum === 0
            ? '<i class="fas fa-credit-card"></i> COBRAR S/ ' + total.toFixed(2)
            : 'Faltan S/ ' + pending.toFixed(2);
}

function closeChargeModal() {
    document.getElementById('chargeOverlay').style.display = 'none';
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

function searchChargeCustomers(term) {
    if (term.length < 2) { document.getElementById('chargeCustomerDropdown').style.display = 'none'; return; }
    const termLower = term.toLowerCase();
    const results = customersData.filter(c => {
        return (c.nombre && c.nombre.toLowerCase().includes(termLower)) || (c.documento_numero && c.documento_numero.includes(term));
    });
    const dropdown = document.getElementById('chargeCustomerDropdown');
    if (results.length === 0) {
        dropdown.innerHTML = '<div style="padding:8px;color:#999;">Sin resultados</div>';
        dropdown.style.display = 'block';
        return;
    }
    let html = '';
    results.slice(0, 10).forEach(customer => {
        html += '<div class="customer-option" onclick="selectChargeCustomer(' + customer.id + ', \'' + customer.nombre.replace(/'/g, "\\'") + '\')">' +
            '<div class="customer-option-name">' + customer.nombre + '</div>' +
            '<div class="customer-option-doc">' + (customer.documento_tipo || '') + ': ' + (customer.documento_numero || '') + '</div></div>';
    });
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function selectChargeCustomer(id, nombre) {
    document.getElementById('chargeCustomerId').value = id;
    document.getElementById('chargeCustomerSearch').value = nombre;
    document.getElementById('chargeCustomerDropdown').style.display = 'none';
}
</script>
@endpush
