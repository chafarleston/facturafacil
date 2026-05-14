<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocina - KDS</title>
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            overflow: hidden; 
            background: #1a1a2e; 
            color: #fff; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .kds-container {
            height: 100vh;
            padding: 10px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .kds-header {
            background: linear-gradient(135deg, #e94560, #c23a51);
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .kds-header h2 { margin: 0; font-size: 18px; }
        .kds-header .time { font-size: 14px; font-weight: bold; }
        
        .kds-header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .kds-stats {
            display: flex;
            gap: 15px;
        }
        
        .kds-stat {
            text-align: center;
        }
        
        .kds-stat-value {
            font-size: 20px;
            font-weight: bold;
        }
        
        .kds-stat-label {
            font-size: 10px;
            opacity: 0.8;
        }
        
        .kds-orders {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 280px));
            grid-auto-rows: max-content;
            gap: 12px;
            flex: 1;
            overflow-y: auto;
            padding-bottom: 10px;
            align-content: start;
        }
        
        .kds-order {
            background: #16213e;
            border-radius: 10px;
            border-left: 4px solid #e94560;
            overflow: hidden;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            max-height: 380px;
        }
        
        .kds-order.ready { border-left-color: #00ff88; }
        .kds-order.sent { border-left-color: #ffc107; }
        
        .kds-order-header {
            background: #1a1a2e;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .kds-order-number { font-size: 14px; font-weight: bold; color: #e94560; }
        .kds-order.ready .kds-order-number { color: #00ff88; }
        .kds-order.sent .kds-order-number { color: #ffc107; }
        
        .kds-order-table { font-size: 12px; color: #888; }
        .kds-order-user { font-size: 10px; color: #4fc3f7; margin-top: 1px; }
        .kds-order-notes { font-size: 10px; color: #ffeb3b; margin-top: 2px; font-style: italic; }
        .kds-order-time { font-size: 10px; color: #666; }
        .kds-order.elapsed .kds-order-time { color: #ff4444; font-weight: bold; }
        
        .kds-items {
            padding: 10px;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        .kds-items::-webkit-scrollbar { width: 6px; }
        .kds-items::-webkit-scrollbar-track { background: transparent; }
        .kds-items::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
        .kds-items::-webkit-scrollbar-thumb:hover { background: #666; }
        
        .kds-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 6px 0;
            border-bottom: 1px dashed #333;
        }
        
        .kds-item:last-child { border-bottom: none; }
        
        .kds-item-qty {
            background: #e94560;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 13px;
            min-width: 36px;
            text-align: center;
        }
        
        .kds-item.q-sent .kds-item-qty { background: #ff9800; color: white; }
        .kds-item.q-ready .kds-item-qty { background: #00ff88; color: #000; }
        .kds-item.q-delivered .kds-item-qty { background: #555; color: #aaa; }
        
        .kds-item-name {
            flex: 1;
            margin-left: 10px;
            font-size: 13px;
            font-weight: bold;
        }
        
        .kds-item-notes {
            font-size: 10px;
            color: #ffc107;
            font-style: italic;
            margin-left: 10px;
        }
        
        .kds-order-actions {
            padding: 6px 8px;
            background: #1a1a2e;
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        
.kds-btn {
            flex: 1;
            min-width: 80px;
            padding: 8px 4px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .kds-btn:hover { transform: scale(1.02); }
        
        .kds-btn-ready { background: #00ff88; color: #000; }
        .kds-btn-deliver { background: #4caf50; color: white; }
        .kds-btn-print { background: #2196f3; color: white; }
        
        .kds-empty {
            text-align: center;
            padding: 50px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .kds-empty i { font-size: 80px; margin-bottom: 20px; }
        .kds-empty h3 { font-size: 24px; }
        
.kds-btn {
            flex: 1;
            padding: 6px 8px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        
        .kds-btn:hover { transform: scale(1.02); filter: brightness(1.1); }
        
        .kds-btn-ready { background: #ff9800; color: white; }
        .kds-btn-deliver { background: #4caf50; color: white; }
        .kds-btn-print { background: #2196f3; color: white; }
        
        .kds-header .btn-dark {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: #333;
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kds-header .btn-dark:hover { background: #555; }
        
        .kds-empty {
            text-align: center;
            padding: 50px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .kds-empty i { font-size: 80px; margin-bottom: 20px; opacity: 0.5; }
        .kds-empty h3 { font-size: 24px; margin-bottom: 10px; }
        .kds-empty p { font-size: 14px; opacity: 0.7; }
        
        .kds-order.pending { border-left-color: #e94560; }
        .kds-order.sent { border-left-color: #ffc107; }
        .kds-order.ready { border-left-color: #00ff88; }
        
        @media (max-width: 768px) {
            .kds-orders {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
            .kds-header {
                flex-direction: column;
                text-align: center;
            }
            .kds-stats {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="kds-container">
        <div class="kds-header">
            <h2><i class="fas fa-utensils"></i> COCINA - KDS</h2>
            <div class="kds-stats">
                <div class="kds-stat">
                    <div class="kds-stat-value" id="pendingCount">0</div>
                    <div class="kds-stat-label">Pendientes</div>
                </div>
                <div class="kds-stat">
                    <div class="kds-stat-value" id="sentCount">0</div>
                    <div class="kds-stat-label">En Cocina</div>
                </div>
                <div class="kds-stat">
                    <div class="kds-stat-value" id="readyCount">0</div>
                    <div class="kds-stat-label">Listos</div>
                </div>
            </div>
            <div class="kds-header-right">
                <button class="btn btn-dark" id="audioBtn" onclick="initAudio(); playAlertSound();" title="Activar sonido">
                    <i class="fas fa-volume-up"></i> Sonido
                </button>
                <div class="kds-stat">
                    <div class="kds-stat-value time" id="currentTime">--:--</div>
                    <div class="kds-stat-label">Hora</div>
                </div>
            </div>
        </div>
        
        <div class="kds-orders" id="kdsOrders">
            <div class="kds-empty">
                <i class="fas fa-utensils"></i>
                <h3>Sin pedidos en cola</h3>
                <p>Los pedidos aparecerán aquí automáticamente</p>
            </div>
        </div>
    </div>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
let allOrders = [];
let audioContext = null;
let alertSound = null;

function initAudio() {
    if (audioContext) return;
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    alertSound = {
        play: function() {
            if (!audioContext) {
                initAudio();
            }
            
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }
            
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();
            
            osc.connect(gain);
            gain.connect(audioContext.destination);
            
            osc.frequency.value = 880;
            osc.type = 'sine';
            
            gain.gain.setValueAtTime(0.3, audioContext.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            osc.start();
            osc.stop(audioContext.currentTime + 0.3);
            
            setTimeout(() => {
                const osc2 = audioContext.createOscillator();
                const gain2 = audioContext.createGain();
                osc2.connect(gain2);
                gain2.connect(audioContext.destination);
                osc2.frequency.value = 1046;
                osc2.type = 'sine';
                gain2.gain.setValueAtTime(0.3, audioContext.currentTime);
                gain2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                osc2.start();
                osc2.stop(audioContext.currentTime + 0.3);
            }, 400);
        }
    };
}

function playAlertSound() {
    if (!alertSound) {
        initAudio();
    }
    if (alertSound) {
        alertSound.play();
    }
}

function updateClock() {
    const now = new Date();
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
}

setInterval(updateClock, 1000);
updateClock();

function loadKitchenOrders() {
    fetch('/restaurant/kitchen-orders?_=' + Date.now())
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const prevCount = allOrders.length;
            allOrders = data.orders;
            renderKitchenOrders();
            
            if (prevCount > 0 && allOrders.length > prevCount) {
                playAlertSound();
            }
        }
    })
    .catch(err => console.error('KDS Error:', err));
}

function getElapsedTime(dateString) {
    const orderDate = new Date(dateString);
    const now = new Date();
    const diffMs = now - orderDate;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return diffMins + ' min';
    const hours = Math.floor(diffMins / 60);
    return hours + 'h ' + (diffMins % 60) + 'm';
}

function isOverdue(dateString) {
    const orderDate = new Date(dateString);
    const now = new Date();
    const diffMins = (now - orderDate) / 60000;
    return diffMins > 15;
}

function renderKitchenOrders() {
    const container = document.getElementById('kdsOrders');
    
    let pendingOrders = allOrders.filter(o => o.status === 'OPEN');
    let sentOrders = allOrders.filter(o => o.status === 'SENT_TO_KITCHEN');
    let readyOrders = allOrders.filter(o => o.status === 'READY');
    
    document.getElementById('pendingCount').textContent = pendingOrders.length;
    document.getElementById('sentCount').textContent = sentOrders.length;
    document.getElementById('readyCount').textContent = readyOrders.length;
    
    const sortedOrders = [...pendingOrders, ...sentOrders, ...readyOrders];
    
    if (sortedOrders.length === 0) {
        container.innerHTML = `
            <div class="kds-empty">
                <i class="fas fa-utensils"></i>
                <h3>Sin pedidos en cola</h3>
                <p>Los pedidos aparecerán aquí automáticamente</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    sortedOrders.forEach(order => {
        const elapsed = getElapsedTime(order.created_at);
        const overdue = isOverdue(order.created_at);
        const statusClass = order.status === 'READY' ? 'ready' : 
                           order.status === 'SENT_TO_KITCHEN' ? 'sent' : 'pending';
        
        const sortedItems = [...order.items].sort((a, b) => {
            const orderVal = { 'PENDING': 0, 'SENT': 1, 'READY': 2, 'DELIVERED': 3 };
            return (orderVal[a.kitchen_status] || 0) - (orderVal[b.kitchen_status] || 0);
        });
        
        html += `
            <div class="kds-order ${statusClass}" data-order-id="${order.id}">
                <div class="kds-order-header">
                    <div>
                        <div class="kds-order-number">${order.order_number}</div>
                        <div class="kds-order-table">
                            <i class="fas fa-chair"></i> ${order.table_name || 'Mesa'}
                            ${order.floor_name ? ` <span style="opacity:0.6;">(${order.floor_name})</span>` : ''}
                        </div>
                        ${order.user_name ? `<div class="kds-order-user"><i class="fas fa-user"></i> ${order.user_name}</div>` : ''}
                        ${order.notes ? `<div class="kds-order-notes"><i class="fas fa-sticky-note"></i> ${order.notes}</div>` : ''}
                    </div>
                    <div class="kds-order-time ${overdue ? 'overdue' : ''}">${elapsed}</div>
                </div>
                <div class="kds-items">
                    ${sortedItems.map(item => {
                        const qClass = item.kitchen_status === 'READY' ? 'q-ready' : item.kitchen_status === 'DELIVERED' ? 'q-delivered' : 'q-sent';
                        return `
                        <div class="kds-item ${qClass}">
                            <span class="kds-item-qty">${item.quantity}x</span>
                            <div>
                                <div class="kds-item-name">${item.product_name}</div>
                                ${item.notes ? `<div class="kds-item-notes">${item.notes}</div>` : ''}
                            </div>
                        </div>`;
                    }).join('')}
                </div>
                <div class="kds-order-actions">
                    ${order.status === 'OPEN' || order.status === 'SENT_TO_KITCHEN' ? `
                        <button class="kds-btn kds-btn-ready" onclick="markOrderReady(${order.id})">
                            <i class="fas fa-check"></i> LISTO
                        </button>
                    ` : ''}
                    ${order.status === 'READY' ? `
                        <button class="kds-btn kds-btn-deliver" onclick="deliverOrder(${order.id})">
                            <i class="fas fa-check-double"></i> ENTREGADO
                        </button>
                    ` : ''}
                    <button class="kds-btn kds-btn-print" onclick="printTicket(${order.id})">
                        <i class="fas fa-print"></i> IMPRIMIR
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function markOrderReady(orderId) {
    fetch('/restaurant/kitchen/' + orderId + '/ready', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadKitchenOrders();
        }
    });
}

function deliverOrder(orderId) {
    fetch('/restaurant/kitchen/' + orderId + '/deliver', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadKitchenOrders();
        }
    });
}

function printTicket(orderId) {
    window.open('/restaurant/orders/' + orderId + '/print-kitchen', '_blank');
}

loadKitchenOrders();
setInterval(loadKitchenOrders, 5000);
    </script>
</body>
</html>