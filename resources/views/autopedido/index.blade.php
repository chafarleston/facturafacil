<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Auto Pedido</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; user-select: none; -webkit-tap-highlight-color: transparent; }
        .header { background: linear-gradient(135deg, #e94560, #c23a51); color: #fff; padding: 15px 20px; text-align: center; font-size: 22px; font-weight: bold; position: sticky; top: 0; z-index: 10; }
        .search-bar { display: flex; gap: 10px; padding: 12px 15px; background: #fff; border-bottom: 1px solid #ddd; }
        .search-input { flex: 1; padding: 14px 16px; font-size: 18px; border: 2px solid #ddd; border-radius: 10px; outline: none; }
        .search-input:focus { border-color: #e94560; }
        .search-btn { padding: 14px 20px; background: #e94560; color: #fff; border: none; border-radius: 10px; font-size: 18px; cursor: pointer; }
        .categories { display: flex; gap: 8px; padding: 10px 15px; overflow-x: auto; background: #fff; border-bottom: 1px solid #eee; white-space: nowrap; }
        .cat-btn { padding: 10px 20px; border: 2px solid #ddd; border-radius: 25px; background: #fff; font-size: 16px; cursor: pointer; transition: all .2s; }
        .cat-btn.active { background: #e94560; color: #fff; border-color: #e94560; }
        .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; padding: 15px; padding-bottom: 100px; }
        .product-card { background: #fff; border-radius: 12px; padding: 15px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,.08); cursor: pointer; transition: transform .15s; }
        .product-card:active { transform: scale(.95); }
        .product-icon { font-size: 40px; margin-bottom: 8px; color: #e94560; }
        .product-name { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .product-price { font-size: 16px; color: #28a745; font-weight: bold; }
        .bottom-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 2px solid #ddd; padding: 12px 20px; display: flex; align-items: center; gap: 15px; z-index: 20; }
        .cart-info { flex: 1; font-size: 16px; }
        .cart-info strong { font-size: 20px; }
        .btn-confirm { padding: 14px 30px; background: #28a745; color: #fff; border: none; border-radius: 10px; font-size: 20px; font-weight: bold; cursor: pointer; }
        .btn-confirm:disabled { background: #ccc; cursor: not-allowed; }
        .cart-items { position: fixed; bottom: 70px; left: 0; right: 0; max-height: 40vh; overflow-y: auto; background: #fff; border-top: 2px solid #e94560; padding: 10px 20px; display: none; z-index: 15; }
        .cart-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eee; }
        .cart-item-name { flex: 1; font-size: 16px; }
        .cart-item-qty { display: flex; align-items: center; gap: 8px; }
        .qty-btn { width: 36px; height: 36px; border: 2px solid #ddd; border-radius: 50%; background: #fff; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .qty-btn:active { background: #eee; }
        .cart-item-price { font-size: 16px; font-weight: bold; min-width: 70px; text-align: right; }
        .btn-remove { color: #dc3545; font-size: 20px; cursor: pointer; padding: 5px; }

        /* Virtual Keyboard */
        .keyboard-overlay { display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 100; background: #d1d5db; padding: 8px; }
        .keyboard-overlay.show { display: block; }
        .kb-row { display: flex; justify-content: center; gap: 4px; margin-bottom: 4px; }
        .kb-key { min-width: 48px; height: 52px; background: #fff; border: 1px solid #999; border-radius: 6px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,.15); }
        .kb-key:active { background: #e0e0e0; }
        .kb-key-wide { min-width: 80px; }
        .kb-key-space { min-width: 240px; }
        .kb-key-action { background: #e94560; color: #fff; border-color: #c23a51; }
        .kb-row-top { margin-bottom: 4px; }

        @media (min-width: 768px) {
            .products { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
            .keyboard-overlay { max-width: 600px; left: 50%; transform: translateX(-50%); border-radius: 12px 12px 0 0; }
        }
    </style>
</head>
<body>
    <div class="header">🍽️ Auto Pedido</div>

    <div class="search-bar">
        <input type="text" class="search-input" id="searchInput" placeholder="Buscar producto..." readonly onfocus="openKeyboard()">
        <button class="search-btn" onclick="openKeyboard()"><i class="fas fa-keyboard"></i></button>
    </div>

    <div class="categories" id="categoriesContainer">
        <button class="cat-btn active" data-cat="all" onclick="filterCategory('all')">🍽️ Todos</button>
        @foreach($categories as $cat)
        <button class="cat-btn" data-cat="{{ $cat->id }}" onclick="filterCategory({{ $cat->id }})">{{ $cat->nombre }}</button>
        @endforeach
    </div>

    <div class="products" id="productsContainer">
        @foreach($products as $p)
        <div class="product-card" data-category="{{ $p->category_id ?? 0 }}" data-name="{{ strtolower($p->descripcion) }}" onclick="addProduct({{ $p->id }}, '{{ addslashes($p->descripcion) }}', {{ $p->precio }})">
            <div class="product-icon"><i class="fas fa-utensils"></i></div>
            <div class="product-name">{{ $p->descripcion }}</div>
            <div class="product-price">S/ {{ number_format($p->precio, 2) }}</div>
        </div>
        @endforeach
    </div>

    <!-- Cart items popup -->
    <div class="cart-items" id="cartItems"></div>

    <!-- Bottom bar -->
    <div class="bottom-bar">
        <div class="cart-info">
            <span id="cartCount">0</span> items · Total: <strong id="cartTotal">S/ 0.00</strong>
            <span style="font-size:13px;color:#e94560;cursor:pointer;margin-left:10px;" onclick="toggleCart()">▼ Ver</span>
        </div>
        <button class="btn-confirm" id="btnConfirm" disabled onclick="confirmOrder()"><i class="fas fa-check"></i> CONFIRMAR</button>
    </div>

    <!-- Virtual Keyboard -->
    <div class="keyboard-overlay" id="keyboard">
        <div class="kb-row kb-row-top">
            <div class="kb-key" onclick="pressKey('1')">1</div>
            <div class="kb-key" onclick="pressKey('2')">2</div>
            <div class="kb-key" onclick="pressKey('3')">3</div>
            <div class="kb-key" onclick="pressKey('4')">4</div>
            <div class="kb-key" onclick="pressKey('5')">5</div>
            <div class="kb-key" onclick="pressKey('6')">6</div>
            <div class="kb-key" onclick="pressKey('7')">7</div>
            <div class="kb-key" onclick="pressKey('8')">8</div>
            <div class="kb-key" onclick="pressKey('9')">9</div>
            <div class="kb-key" onclick="pressKey('0')">0</div>
            <div class="kb-key kb-key-wide kb-key-action" onclick="pressBackspace()">⌫</div>
        </div>
        <div class="kb-row">
            <div class="kb-key" onclick="pressKey('Q')">Q</div>
            <div class="kb-key" onclick="pressKey('W')">W</div>
            <div class="kb-key" onclick="pressKey('E')">E</div>
            <div class="kb-key" onclick="pressKey('R')">R</div>
            <div class="kb-key" onclick="pressKey('T')">T</div>
            <div class="kb-key" onclick="pressKey('Y')">Y</div>
            <div class="kb-key" onclick="pressKey('U')">U</div>
            <div class="kb-key" onclick="pressKey('I')">I</div>
            <div class="kb-key" onclick="pressKey('O')">O</div>
            <div class="kb-key" onclick="pressKey('P')">P</div>
        </div>
        <div class="kb-row">
            <div class="kb-key" onclick="pressKey('A')">A</div>
            <div class="kb-key" onclick="pressKey('S')">S</div>
            <div class="kb-key" onclick="pressKey('D')">D</div>
            <div class="kb-key" onclick="pressKey('F')">F</div>
            <div class="kb-key" onclick="pressKey('G')">G</div>
            <div class="kb-key" onclick="pressKey('H')">H</div>
            <div class="kb-key" onclick="pressKey('J')">J</div>
            <div class="kb-key" onclick="pressKey('K')">K</div>
            <div class="kb-key" onclick="pressKey('L')">L</div>
            <div class="kb-key" onclick="pressKey('Ñ')">Ñ</div>
        </div>
        <div class="kb-row">
            <div class="kb-key" onclick="pressKey('Z')">Z</div>
            <div class="kb-key" onclick="pressKey('X')">X</div>
            <div class="kb-key" onclick="pressKey('C')">C</div>
            <div class="kb-key" onclick="pressKey('V')">V</div>
            <div class="kb-key" onclick="pressKey('B')">B</div>
            <div class="kb-key" onclick="pressKey('N')">N</div>
            <div class="kb-key" onclick="pressKey('M')">M</div>
            <div class="kb-key kb-key-wide" onclick="pressKey(' ')" style="min-width:120px;">Espacio</div>
            <div class="kb-key kb-key-action" onclick="closeKeyboard()"><i class="fas fa-check"></i> OK</div>
        </div>
    </div>

    <script>
        let cart = [];
        let activeCategory = 'all';
        let showCart = false;

        function addProduct(id, name, price) {
            const existing = cart.find(c => c.product_id === id);
            if (existing) {
                existing.quantity++;
            } else {
                cart.push({ product_id: id, name, price, quantity: 1 });
            }
            updateCart();
        }

        function updateCart() {
            const count = cart.reduce((s, c) => s + c.quantity, 0);
            const total = cart.reduce((s, c) => s + c.price * c.quantity, 0);
            document.getElementById('cartCount').textContent = count;
            document.getElementById('cartTotal').textContent = 'S/ ' + total.toFixed(2);
            document.getElementById('btnConfirm').disabled = count === 0;

            const container = document.getElementById('cartItems');
            if (cart.length === 0) {
                container.innerHTML = '<div style="padding:15px;text-align:center;color:#999;">Carrito vacío</div>';
                return;
            }
            let html = '';
            cart.forEach((c, i) => {
                html += `<div class="cart-item">
                    <span class="cart-item-name">${c.name}</span>
                    <div class="cart-item-qty">
                        <button class="qty-btn" onclick="changeQty(${i}, -1)">−</button>
                        <span style="font-size:18px;font-weight:bold;">${c.quantity}</span>
                        <button class="qty-btn" onclick="changeQty(${i}, 1)">+</button>
                    </div>
                    <span class="cart-item-price">S/ ${(c.price * c.quantity).toFixed(2)}</span>
                    <span class="btn-remove" onclick="removeItem(${i})"><i class="fas fa-trash"></i></span>
                </div>`;
            });
            container.innerHTML = html;
        }

        function changeQty(idx, delta) {
            cart[idx].quantity = Math.max(1, cart[idx].quantity + delta);
            updateCart();
        }

        function removeItem(idx) {
            cart.splice(idx, 1);
            updateCart();
        }

        function toggleCart() {
            showCart = !showCart;
            document.getElementById('cartItems').style.display = showCart ? 'block' : 'none';
        }

        function filterCategory(catId) {
            activeCategory = catId;
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.toggle('active', b.dataset.cat == catId));
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('searchInput').value.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach(card => {
                const catMatch = activeCategory === 'all' || card.dataset.category == activeCategory;
                const nameMatch = !q || card.dataset.name.includes(q);
                card.style.display = (catMatch && nameMatch) ? '' : 'none';
            });
        }

        /* Virtual Keyboard */
        function openKeyboard() {
            document.getElementById('keyboard').classList.add('show');
            document.getElementById('searchInput').focus();
        }
        function closeKeyboard() {
            document.getElementById('keyboard').classList.remove('show');
        }
        function pressKey(k) {
            const input = document.getElementById('searchInput');
            input.value += k;
            input.focus();
            applyFilters();
        }
        function pressBackspace() {
            const input = document.getElementById('searchInput');
            input.value = input.value.slice(0, -1);
            input.focus();
            applyFilters();
        }

        /* Confirm order */
        function confirmOrder() {
            if (cart.length === 0) return;
            const btn = document.getElementById('btnConfirm');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch('{{ route("autopedido.confirm") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ items: JSON.stringify(cart.map(c => ({ product_id: c.product_id, quantity: c.quantity }))) })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/autopedido/success/' + data.order_id;
                } else {
                    alert(data.message || 'Error al confirmar');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> CONFIRMAR';
                }
            })
            .catch(err => {
                alert('Error de conexión');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> CONFIRMAR';
            });
        }
    </script>
</body>
</html>
