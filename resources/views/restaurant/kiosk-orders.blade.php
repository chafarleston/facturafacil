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
                    <th>Estado</th>
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
                    <td>
                        @if($order->status === 'PENDING_PAYMENT')
                        <span class="badge badge-warning">Pendiente</span>
                        @else
                        <span class="badge badge-info">En Cocina</span>
                        @endif
                    </td>
                    <td>{{ $order->created_at->format('d/m H:i') }}</td>
                    <td class="text-right">
                        @if($order->status === 'PENDING_PAYMENT')
                        <button class="btn btn-warning btn-sm" onclick="sendKioskToKitchen({{ $order->id }})">
                            <i class="fas fa-utensils"></i> Enviar a Cocina
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="cancelKioskOrder({{ $order->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                        @else
                        <button class="btn btn-success btn-sm" onclick="chargeKioskOrder({{ $order->id }}, {{ $order->total }})">
                            <i class="fas fa-cash-register"></i> Cobrar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="cancelKioskOrder({{ $order->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">No hay pedidos kiosko pendientes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Cancel Confirm Modal --}}
<div class="cancel-overlay" id="cancelOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10001; align-items:center; justify-content:center;">
    <div class="cancel-popup" style="background:white; padding:25px; border-radius:10px; min-width:380px; max-width:450px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h5 style="margin:0; color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> Anular Pedido</h5>
            <button onclick="closeCancelModal()" style="border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:12px; margin-bottom:15px;">
            <div style="font-size:13px; color:#856404; margin-bottom:4px;"><strong><i class="fas fa-info-circle"></i> Pedido:</strong> <span id="cancelOrderNumber">-</span></div>
            <div style="font-size:13px; color:#856404;" id="cancelOrderItems"></div>
        </div>
        <p style="color:#666; font-size:13px; margin-bottom:10px;">¿Está seguro de anular este pedido? Esta acción no se puede deshacer.</p>
        <div id="cancelAdminSection" style="display:none; margin-bottom:12px;">
            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Contraseña de Administrador</label>
            <input type="password" id="cancelAdminPassword" class="form-control form-control-sm" placeholder="Requerida para pedidos en cocina" style="border-color:#dc3545;">
        </div>
        <div id="cancelError" style="display:none; color:#dc3545; font-size:12px; margin-bottom:10px;"></div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-secondary btn-sm" onclick="closeCancelModal()" style="flex:1;">Cancelar</button>
            <button class="btn btn-danger btn-sm" id="btnConfirmCancel" onclick="confirmCancelKioskOrder()" style="flex:1;">
                <i class="fas fa-trash"></i> Sí, Anular
            </button>
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
            <div style="position:relative;">
                <input type="text" id="chargeCustomerSearch" class="form-control form-control-sm" placeholder="Buscar cliente..." autocomplete="off">
                <input type="hidden" id="chargeCustomerId" value="">
                <div id="chargeCustomerDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ddd; border-radius:4px; max-height:200px; overflow-y:auto; z-index:999;"></div>
            </div>
        </div>
        <div style="display:flex; gap:8px; margin-bottom:12px;">
            <div style="flex:1;"><label>Tipo Documento</label><select id="chargeDocumentType" class="form-control form-control-sm" onchange="updateChargeSerie()"><option value="03">BOLETA</option><option value="01">FACTURA</option><option value="NV" selected>NOTA DE VENTA</option></select></div>
            <div style="flex:1;"><label>Serie</label><input type="text" id="chargeSerieDisplay" class="form-control form-control-sm" readonly disabled></div>
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
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;"><span>IGV:</span><span id="chargeIgv">S/ 0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:bold; margin-top:8px;"><span>TOTAL:</span><span id="chargeTotal">S/ 0.00</span></div>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn btn-secondary btn-sm" onclick="closeChargeModal()" style="flex:0 0 80px;">Cancelar</button>
            <button class="btn btn-success btn-sm" id="btnProcessCharge" onclick="processCharge()" style="flex:1; padding:8px 0;"><i class="fas fa-credit-card"></i> COBRAR S/ 0.00</button>
        </div>
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

function sendKioskToKitchen(orderId) {
    if (!confirm('¿Enviar este pedido a cocina?')) return;
    const btn = document.querySelector(`button[onclick="sendKioskToKitchen(${orderId})"]`);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }

    fetch('/restaurant/kiosk-send/' + orderId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-utensils"></i> Enviar a Cocina'; }
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-utensils"></i> Enviar a Cocina'; }
    });
}

let pendingCancelOrderId = null;
let pendingCancelRequiresAdmin = false;

function cancelKioskOrder(orderId) {
    pendingCancelOrderId = orderId;
    pendingCancelRequiresAdmin = false;
    document.getElementById('cancelAdminSection').style.display = 'none';
    document.getElementById('cancelAdminPassword').value = '';
    document.getElementById('cancelError').style.display = 'none';
    document.getElementById('btnConfirmCancel').disabled = false;
    document.getElementById('btnConfirmCancel').innerHTML = '<i class="fas fa-trash"></i> Sí, Anular';

    // Find order info from the table
    const rows = document.querySelectorAll('tbody tr');
    let orderNumber = '';
    let itemsHtml = '';
    let requiresAdmin = false;
    rows.forEach(function(row) {
        const btn = row.querySelector('button[onclick*="cancelKioskOrder(' + orderId + ')"]');
        if (btn) {
            const cells = row.querySelectorAll('td');
            orderNumber = cells[0].textContent.trim();
            itemsHtml = cells[1].innerHTML;
            // Si tiene badge "En Cocina" requiere admin
            requiresAdmin = cells[3].textContent.includes('En Cocina');
        }
    });

    document.getElementById('cancelOrderNumber').textContent = orderNumber;
    document.getElementById('cancelOrderItems').innerHTML = itemsHtml;

    if (requiresAdmin) {
        pendingCancelRequiresAdmin = true;
        document.getElementById('cancelAdminSection').style.display = 'block';
    }

    document.getElementById('cancelOverlay').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelOverlay').style.display = 'none';
    pendingCancelOrderId = null;
}

function confirmCancelKioskOrder() {
    if (!pendingCancelOrderId) return;
    
    const btn = document.getElementById('btnConfirmCancel');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Anulando...';
    document.getElementById('cancelError').style.display = 'none';
    
    const payload = {};
    if (pendingCancelRequiresAdmin) {
        const pwd = document.getElementById('cancelAdminPassword').value;
        if (!pwd) {
            document.getElementById('cancelError').textContent = 'Debe ingresar la contraseña de administrador.';
            document.getElementById('cancelError').style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Sí, Anular';
            return;
        }
        payload.admin_password = pwd;
    }

    fetch('/restaurant/orders/' + pendingCancelOrderId + '/cancel', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeCancelModal();
            location.reload();
        } else {
            document.getElementById('cancelError').textContent = data.message || 'Error al anular el pedido';
            document.getElementById('cancelError').style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Sí, Anular';
        }
    })
    .catch(err => {
        document.getElementById('cancelError').textContent = 'Error: ' + err.message;
        document.getElementById('cancelError').style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Sí, Anular';
    });
}

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
            'Content-Type': 'application/json',
            'Accept': 'application/json'
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

document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('chargeCustomerSearch');
    if (search) {
        search.addEventListener('input', function(e) { searchChargeCustomers(e.target.value); });
        search.addEventListener('blur', function() { setTimeout(function() { document.getElementById('chargeCustomerDropdown').style.display = 'none'; }, 200); });
    }
});
</script>
@endpush
