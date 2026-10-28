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
        .kds-order.preparing { border-left-color: #ff9800; }
        
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
        .kds-order.preparing .kds-order-number { color: #ff9800; }
        
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
        .kds-item.q-cancelled { opacity: 0.5; }
        .kds-item.q-cancelled .kds-item-name { text-decoration: line-through; color: #ff4444; }
        .kds-item.q-cancelled .kds-item-qty { background: #cc0000; color: white; }
        
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
        .kds-btn-preparing { background: #ff9800; color: white; }
        .kds-btn-deliver { background: #4caf50; color: white; }
        .kds-btn-print { background: #2196f3; color: white; }
        .kds-btn-complete { background: #ff9800; color: white; }
        
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
        .kds-order.kiosko { border-left-color: #9c27b0; }
        
        .kds-section-title {
            grid-column: 1 / -1;
            font-size: 16px;
            font-weight: bold;
            padding: 8px 0 4px 0;
            border-bottom: 2px solid #333;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .kds-section-title .kiosko-badge {
            background: #9c27b0;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        .kds-section-title .mozo-badge {
            background: #e94560;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        .kds-tag-kiosko {
            display: inline-block;
            background: #9c27b0;
            color: #fff;
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 8px;
            margin-left: 6px;
            vertical-align: middle;
        }
        
        .kds-order-batches {
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        .kds-batch {
            background: #101a30;
            border: 1px solid #2d3a5a;
            border-radius: 8px;
            overflow: hidden;
        }

        .kds-batch.new {
            border-color: #ffc107;
            box-shadow: 0 0 0 1px rgba(255, 193, 7, 0.35);
        }

        .kds-batch-header {
            padding: 6px 8px;
            background: #1a2540;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .kds-batch-heading {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .kds-batch-title {
            font-size: 11px;
            font-weight: 700;
            color: #ffc107;
            letter-spacing: 0.3px;
        }

        .kds-batch.new .kds-batch-title { color: #ffeb3b; }

        .kds-batch-status {
            font-size: 9px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 8px;
            background: #333;
            color: #ddd;
        }

        .kds-batch-status.preparing { background: #ff9800; color: #000; }
        .kds-batch-status.pending { background: #ffc107; color: #000; }

        .kds-batch-new-label {
            font-size: 8px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 8px;
            background: #ffeb3b;
            color: #000;
            letter-spacing: 0.3px;
        }

        .kds-batch-time { font-size: 10px; color: #888; white-space: nowrap; }
        .kds-batch-time.overdue { color: #ff4444; font-weight: 700; }

        .kds-batch-items { padding: 2px 8px; }

        .kds-batch-actions {
            padding: 4px 6px;
            background: #1a1a2e;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .kds-batch-actions .kds-btn {
            font-size: 10px;
            padding: 5px 4px;
            min-width: 0;
        }

        .kds-order.new-batch {
            border-color: #ffc107;
            box-shadow: 0 0 0 1px rgba(255, 193, 7, 0.35);
        }

        .kds-batch-tag {
            display: inline-block;
            background: #333;
            color: #ddd;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 8px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .kds-batch-tag.new {
            background: #ffc107;
            color: #000;
        }

        .kds-order-batch-status {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 3px;
        }

        .kds-order-time.overdue {
            color: #ff4444;
            font-weight: 700;
        }

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
    <div id="kdsInactiveNotice" style="display:none; position:fixed; top:0; left:0; right:0; z-index:9999; background:#e94560; color:#fff; text-align:center; padding:12px; font-size:16px; font-weight:bold;">
        KDS INACTIVO - Modo Impresión 80mm activo. Active el Modo KDS para usar esta pantalla.
        <button type="button" onclick="location.reload()" style="margin-left:12px; padding:6px 16px; border:none; border-radius:20px; background:#fff; color:#e94560; font-weight:bold; font-size:14px; cursor:pointer; vertical-align:middle;">
            <i class="fas fa-sync-alt"></i> Reintentar
        </button>
    </div>
    <div id="audioUnlockNotice" style="display:none; position:fixed; top:0; left:0; right:0; z-index:99999; background:#ff9800; color:#000; text-align:center; padding:12px; font-size:16px; font-weight:700; cursor:pointer;">
        <i class="fas fa-volume-up"></i> Toque la pantalla para activar el sonido de los pedidos
    </div>
    <div class="kds-container">
        <div class="kds-header">
            <h2><i class="fas fa-utensils"></i> {{ $kds === 'bar' ? 'KDS BAR' : ($kds === 'cocina2' ? 'KDS COCINA 2' : 'KDS COCINA') }}</h2>
            <div class="kds-stats">
                <div class="kds-stat">
                    <div class="kds-stat-value" id="pendingCount">0</div>
                    <div class="kds-stat-label">Lotes pendientes</div>
                </div>
                <div class="kds-stat">
                    <div class="kds-stat-value" id="preparingCount">0</div>
                    <div class="kds-stat-label">Lotes en preparación</div>
                </div>
            </div>
            <div class="kds-header-right">
                <a href="{{ route('restaurant.kitchenHistory') }}" class="btn btn-dark" target="_blank" title="Historial y métricas de cocina">
                    <i class="fas fa-chart-line"></i> Historial
                </a>
                <button class="btn btn-dark" id="audioBtn" onclick="enableAudio()" title="Activar sonido">
                    <i class="fas fa-volume-up"></i> Activar sonido
                </button>
                <div class="kds-stat" id="kdsConnectionWarning" style="display:none;">
                    <div class="kds-stat-value" style="color:#e94560; font-size:13px;"><i class="fas fa-wifi"></i></div>
                    <div class="kds-stat-label">Sin conexión</div>
                </div>
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
let kdsFilter = '{{ $kds ?? "cocina" }}';
let orderMode = @json($orderMode ?? 'kds');
let allOrders = [];
let prevBatchIds = new Set();
let kdsInitialLoad = true;
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
                audioContext.resume().catch(() => {});
            }

            const tone = function (frequency, delay) {
                setTimeout(function () {
                    const osc = audioContext.createOscillator();
                    const gain = audioContext.createGain();
                    osc.connect(gain);
                    gain.connect(audioContext.destination);
                    osc.frequency.value = frequency;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.6, audioContext.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.35);
                    osc.start();
                    osc.stop(audioContext.currentTime + 0.35);
                }, delay);
            };

            tone(880, 0);
            tone(1046, 400);
            tone(1318, 800);
        }
    };
}

function playAlertSound() {
    if (!audioContext || audioContext.state !== 'running') {
        return;
    }
    if (!alertSound) {
        initAudio();
    }
    if (alertSound) {
        alertSound.play();
    }
}

function updateAudioState() {
    const btn = document.getElementById('audioBtn');
    const notice = document.getElementById('audioUnlockNotice');
    const running = audioContext && audioContext.state === 'running';

    if (btn) {
        btn.classList.toggle('btn-success', running);
        btn.classList.toggle('btn-dark', !running);
        btn.innerHTML = running
            ? '<i class="fas fa-volume-up"></i> Sonido activo'
            : '<i class="fas fa-volume-up"></i> Activar sonido';
    }

    if (notice) {
        notice.style.display = running ? 'none' : 'block';
    }

    if (running) {
        localStorage.setItem('kds_sound_enabled', '1');
    }
}

function enableAudio() {
    if (!audioContext) {
        initAudio();
    }
    if (!audioContext) {
        return;
    }

    if (audioContext.state === 'suspended') {
        audioContext.resume().then(function () {
            updateAudioState();
            playAlertSound();
        }).catch(function () {
            updateAudioState();
        });
    } else {
        updateAudioState();
        playAlertSound();
    }
}

// Intento de habilitar el audio al cargar (si el navegador lo permite).
(function () {
    initAudio();
    if (audioContext && audioContext.state === 'suspended') {
        audioContext.resume().catch(function () {});
    }
    updateAudioState();
})();

// El navegador exige una interacción: cualquier clic/toque reanuda el audio.
document.addEventListener('click', function (event) {
    if (event.target && event.target.closest && event.target.closest('#audioBtn')) {
        return;
    }

    if (!audioContext) {
        initAudio();
    }
    if (!audioContext) {
        return;
    }

    if (audioContext.state === 'suspended') {
        audioContext.resume().then(function () {
            updateAudioState();
            playAlertSound();
        }).catch(function () {
            updateAudioState();
        });
    } else {
        updateAudioState();
    }
});

function updateClock() {
    const now = new Date();
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
}

setInterval(updateClock, 1000);
updateClock();

function handlePollResponse(res) {
    if (res.status === 401) {
        window.location.href = '/login';
        return Promise.reject('unauthenticated');
    }
    return res.json();
}

let kdsPolling = false;
let kdsPollFailures = 0;

function showKdsConnectionWarning() {
    const el = document.getElementById('kdsConnectionWarning');
    if (el) el.style.display = 'block';
}

function hideKdsConnectionWarning() {
    const el = document.getElementById('kdsConnectionWarning');
    if (el) el.style.display = 'none';
}

function loadKitchenOrders() {
    if (kdsPolling || document.hidden) return;
    kdsPolling = true;

    fetch('/restaurant/kitchen-orders?_=' + Date.now() + '&kds=' + kdsFilter, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(handlePollResponse)
    .then(data => {
        if (data.order_mode && data.order_mode !== 'kds') {
            stopKdsPolling();
            return;
        }
        if (data.success) {
            const orders = data.orders || [];
            const hasNewBatches = orders.some(order =>
                (order.batches || []).some(batch => !prevBatchIds.has(batch.id))
            );

            allOrders = orders;
            prevBatchIds = new Set(
                orders.flatMap(order => (order.batches || []).map(batch => batch.id))
            );

            renderKitchenOrders();
            kdsPollFailures = 0;
            hideKdsConnectionWarning();

            if (!kdsInitialLoad && hasNewBatches) {
                playAlertSound();
            }

            kdsInitialLoad = false;
        }
    })
    .catch(err => {
        console.error('KDS Error:', err);
        kdsPollFailures++;
        if (kdsPollFailures >= 3) showKdsConnectionWarning();
    })
    .finally(() => {
        kdsPolling = false;
    });
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
    if (!container) return;

    const cards = [];
    allOrders.forEach(order => {
        (order.batches || []).forEach(batch => {
            cards.push({ order, batch });
        });
    });

    const pendingCards = cards
        .filter(card => card.batch.status === 'PENDING')
        .sort((a, b) => new Date(b.batch.sent_at || 0) - new Date(a.batch.sent_at || 0));

    const preparingCards = cards
        .filter(card => card.batch.status === 'PREPARING')
        .sort((a, b) => new Date(a.batch.preparing_started_at || a.batch.sent_at || 0)
            - new Date(b.batch.preparing_started_at || b.batch.sent_at || 0));

    document.getElementById('pendingCount').textContent = pendingCards.length;
    document.getElementById('preparingCount').textContent = preparingCards.length;

    const sortedCards = [...pendingCards, ...preparingCards];

    if (sortedCards.length === 0) {
        container.innerHTML = `
            <div class="kds-empty">
                <i class="fas fa-utensils"></i>
                <h3>Sin pedidos en cola</h3>
                <p>Los pedidos aparecerán aquí automáticamente</p>
            </div>
        `;
        return;
    }

    const mozoCards = sortedCards.filter(card => card.order.order_type !== 'kiosko');
    const kioskoCards = sortedCards.filter(card => card.order.order_type === 'kiosko');

    let html = '';

    if (mozoCards.length > 0) {
        html += `<div class="kds-section-title">
            <span class="mozo-badge">MOZO</span> Lotes de Mesas (${mozoCards.length})
        </div>`;
        mozoCards.forEach(card => { html += renderBatchCard(card.order, card.batch); });
    }

    if (kioskoCards.length > 0) {
        html += `<div class="kds-section-title">
            <span class="kiosko-badge">KIOSKO</span> Lotes de Autoservicio (${kioskoCards.length})
        </div>`;
        kioskoCards.forEach(card => { html += renderBatchCard(card.order, card.batch); });
    }

    container.innerHTML = html;
}

function renderBatchCard(order, batch) {
    const isKiosko = order.order_type === 'kiosko';
    const isPreparing = batch.status === 'PREPARING';
    const isNew = batch.status === 'PENDING' && (batch.batch_number || 0) > 1;
    const sentRecently = batch.sent_at && (Date.now() - new Date(batch.sent_at).getTime()) < 10 * 60 * 1000;
    const startedAt = isPreparing ? (batch.preparing_started_at || batch.sent_at) : batch.sent_at;
    const elapsed = getElapsedTime(startedAt);
    const overdue = isOverdue(startedAt);
    const statusClass = isPreparing ? 'preparing' : 'sent';
    const kioskoClass = isKiosko ? 'kiosko' : '';
    const newClass = isNew ? 'new-batch' : '';

    const sortedItems = [...(batch.items || [])].sort((a, b) => {
        const orderVal = { 'PENDING': 0, 'SENT': 1, 'READY': 2, 'DELIVERED': 3, 'CANCELLED': 4 };
        return (orderVal[a.kitchen_status] || 0) - (orderVal[b.kitchen_status] || 0);
    });

    return `
        <div class="kds-order ${statusClass} ${kioskoClass} ${newClass}" data-order-id="${order.id}" data-batch-id="${batch.id}">
            <div class="kds-order-header">
                <div>
                    <div class="kds-order-number">
                        ${order.order_number}
                        <span class="kds-batch-tag ${isNew ? 'new' : ''}">${isNew ? 'NUEVO LOTE' : 'LOTE'} ${batch.batch_number}</span>
                        ${isKiosko ? `<span class="kds-tag-kiosko"><i class="fas fa-shopping-cart"></i> KIOSKO</span>` : ''}
                    </div>
                    <div class="kds-order-table">
                        <i class="fas ${isKiosko ? 'fa-shopping-cart' : 'fa-chair'}"></i> ${isKiosko ? 'Autoservicio' : (order.table_name || 'Mesa')}
                        ${!isKiosko && order.floor_name ? ` <span style="opacity:0.6;">(${order.floor_name})</span>` : ''}
                    </div>
                    ${order.user_name ? `<div class="kds-order-user"><i class="fas fa-user"></i> ${order.user_name}</div>` : ''}
                    ${order.notes ? `<div class="kds-order-notes"><i class="fas fa-sticky-note"></i> ${order.notes}</div>` : ''}
                    <div class="kds-order-batch-status">
                        <span class="kds-batch-status ${isPreparing ? 'preparing' : 'pending'}">
                            ${isPreparing ? 'EN PREPARACIÓN' : 'PENDIENTE'}
                        </span>
                        ${isNew && sentRecently ? '<span class="kds-batch-new-label">RECIÉN ENVIADO</span>' : ''}
                    </div>
                </div>
                <div class="kds-order-time ${overdue ? 'overdue' : ''}">${elapsed}</div>
            </div>
            <div class="kds-items">
                ${sortedItems.map(item => {
                    const qClass = item.kitchen_status === 'READY' ? 'q-ready' : item.kitchen_status === 'DELIVERED' ? 'q-delivered' : item.kitchen_status === 'CANCELLED' ? 'q-cancelled' : 'q-sent';
                    return `
                    <div class="kds-item ${qClass}">
                        <span class="kds-item-qty">${item.quantity}x</span>
                        <div>
                            <div class="kds-item-name">${item.product_name}</div>
                            ${item.notes ? `<div class="kds-item-notes">${item.notes}</div>` : ''}
                            ${item.auxiliary_names && item.auxiliary_names.length > 0 ? `<div class="kds-item-aux" style="font-size:10px;color:#ce93d8;margin-top:2px;">+ ${item.auxiliary_names.join(', ')}</div>` : ''}
                        </div>
                    </div>`;
                }).join('')}
            </div>
            <div class="kds-order-actions">
                ${batch.status === 'PENDING' ? `
                    <button class="kds-btn kds-btn-preparing" onclick="prepareBatch(${batch.id})">
                        <i class="fas fa-fire"></i> PREPARAR LOTE
                    </button>
                ` : ''}
                ${batch.status === 'PREPARING' ? `
                    <button class="kds-btn kds-btn-deliver" onclick="deliverBatch(${batch.id})">
                        <i class="fas fa-check-double"></i> TERMINAR LOTE
                    </button>
                ` : ''}
                <button class="kds-btn kds-btn-print" onclick="printBatch(${batch.id})">
                    <i class="fas fa-print"></i> IMPRIMIR
                </button>
            </div>
        </div>
    `;
}
function postBatchAction(batchId, action) {
    return fetch('/restaurant/kitchen-batches/' + batchId + '/' + action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(res => res.json());
}

function prepareBatch(batchId) {
    postBatchAction(batchId, 'preparing')
        .then(data => {
            if (data.success) {
                loadKitchenOrders();
            } else {
                alert(data.message || 'Error al preparar el lote');
            }
        })
        .catch(err => {
            console.error('KDS Error:', err);
            alert('Error de conexión al preparar el lote');
        });
}

function deliverBatch(batchId) {
    postBatchAction(batchId, 'deliver')
        .then(data => {
            if (data.success) {
                loadKitchenOrders();
            } else {
                alert(data.message || 'Error al terminar el lote');
            }
        })
        .catch(err => {
            console.error('KDS Error:', err);
            alert('Error de conexión al terminar el lote');
        });
}

function printBatch(batchId) {
    window.open('/restaurant/kitchen-batches/' + batchId + '/print', '_blank');
}
function showKdsInactive() {
    const n = document.getElementById('kdsInactiveNotice');
    if (n) n.style.display = 'block';

    const notice = document.getElementById('audioUnlockNotice');
    if (notice) notice.style.display = 'none';
}

function stopKdsPolling() {
    if (window.__kdsInterval) {
        clearInterval(window.__kdsInterval);
        window.__kdsInterval = null;
    }
    showKdsInactive();
}

if (orderMode === 'kds') {
    loadKitchenOrders();
    window.__kdsInterval = setInterval(loadKitchenOrders, 5000);
} else {
    stopKdsPolling();
}

document.addEventListener('visibilitychange', function () {
    if (!document.hidden && window.__kdsInterval) {
        loadKitchenOrders();

        if (audioContext && audioContext.state === 'suspended') {
            audioContext.resume().then(updateAudioState).catch(updateAudioState);
        }
    }
});
    </script>
</body>
</html>
