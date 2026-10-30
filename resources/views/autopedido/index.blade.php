<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $web['company_name'] }} · Autopedido</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $web['fontUrl'] }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e94560;
            --secondary: #3a1c4a;
            --bg: #f6f7fb;
            --surface: #ffffff;
            --surface-2: #f1f5f9;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --danger: #dc2626;
            --success: #16a34a;
            --radius: 20px;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.10);
            --shadow-sm: 0 8px 22px rgba(15, 23, 42, 0.07);
            --brand-font: 'Poppins', sans-serif;
        }

        body[data-theme="minimal"] {
            --bg: #ffffff;
            --surface: #ffffff;
            --surface-2: #fafafa;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --radius: 14px;
            --shadow: none;
            --shadow-sm: none;
        }

        body[data-theme="elegant"] {
            --bg: #faf7f2;
            --surface: #ffffff;
            --surface-2: #f5efe7;
            --text: #2c241d;
            --muted: #8a7b6c;
            --border: #e7ded2;
            --radius: 16px;
            --shadow: 0 18px 40px rgba(74, 52, 34, 0.09);
            --shadow-sm: 0 8px 22px rgba(74, 52, 34, 0.06);
        }

        body[data-theme="classic"] {
            --bg: #f4f6f8;
            --surface: #ffffff;
            --surface-2: #eef2f5;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #d1d5db;
            --radius: 12px;
        }

        body[data-theme="dark"] {
            --bg: #0f172a;
            --surface: #1e293b;
            --surface-2: #111827;
            --text: #f8fafc;
            --muted: #94a3b8;
            --border: #334155;
            --danger: #f87171;
            --success: #4ade80;
            --shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
            --shadow-sm: 0 8px 22px rgba(0, 0, 0, 0.25);
            color-scheme: dark;
        }

        body[data-theme="tropical"] {
            --bg: #fff7ed;
            --surface: #ffffff;
            --surface-2: #ffedd5;
            --text: #431407;
            --muted: #9a3412;
            --border: #fed7aa;
            --radius: 22px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            font-family: var(--brand-font);
            background: var(--bg);
            color: var(--text);
            padding-bottom: calc(92px + env(safe-area-inset-bottom));
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            -webkit-font-smoothing: antialiased;
        }

        button, input, textarea { font: inherit; color: inherit; }
        button { border: 0; background: transparent; cursor: pointer; }
        img { display: block; max-width: 100%; }

        button:focus-visible, input:focus-visible, textarea:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--primary) 45%, transparent);
            outline-offset: 2px;
        }

        .app-header {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 16px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 2px 14px rgba(15, 23, 42, 0.04);
        }

        .brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .brand-logo { max-height: 64px; width: auto; object-fit: contain; }
        .brand-name { display: flex; flex-direction: column; min-width: 0; }
        .brand-name strong { font-size: 17px; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .brand-name span { font-size: 11px; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; }

        .header-cart {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border: 1px solid var(--border);
            border-radius: 15px;
            background: var(--surface-2);
            color: var(--text);
            font-size: 18px;
            flex: 0 0 auto;
        }

        .header-cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 21px;
            height: 21px;
            padding: 0 5px;
            border-radius: 999px;
            background: var(--primary);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .app-main { width: min(1200px, 100%); margin: 0 auto; padding: 16px; }

        .hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px;
            margin-bottom: 16px;
            border-radius: calc(var(--radius) + 6px);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: var(--shadow);
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            right: -80px;
            top: -100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .hero-copy { position: relative; z-index: 1; max-width: 560px; }
        .hero-kicker { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; opacity: 0.9; margin-bottom: 8px; }
        .hero h1 { font-size: clamp(24px, 5vw, 40px); line-height: 1.08; letter-spacing: -0.03em; margin-bottom: 8px; }
        .hero p { font-size: 14px; line-height: 1.5; opacity: 0.85; max-width: 480px; }
        .hero-mark { position: relative; z-index: 1; font-size: 64px; opacity: 0.22; flex: 0 0 auto; }

        .search-wrap { margin-bottom: 12px; }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            color: var(--muted);
        }

        .search-box > i { font-size: 15px; }
        .search-box input { flex: 1; min-width: 0; padding: 14px 0; border: 0; outline: none; background: transparent; color: var(--text); font-size: 16px; }
        .search-box input::placeholder { color: var(--muted); }
        .search-clear, .search-kb { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; color: var(--muted); }
        .search-clear { display: none; color: var(--danger); }
        .search-kb:hover, .search-clear:hover { background: var(--surface-2); }

        .categories {
            display: flex;
            gap: 8px;
            padding: 2px 0 12px;
            overflow-x: auto;
            scrollbar-width: none;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
        }
        .categories::-webkit-scrollbar { display: none; }

        .cat-btn {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            scroll-snap-align: start;
            transition: background 0.2s, color 0.2s, border-color 0.2s, transform 0.15s;
        }
        .cat-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 8px 18px color-mix(in srgb, var(--primary) 28%, transparent); }
        .cat-btn:active { transform: scale(0.97); }
        .cat-img { width: 26px; height: 26px; border-radius: 8px; object-fit: cover; }

        .categories.categories-only { display: grid; grid-template-columns: repeat(auto-fill, minmax(128px, 1fr)); gap: 12px; overflow: visible; }
        .categories.categories-only .cat-btn {
            flex-direction: column;
            justify-content: center;
            min-height: 138px;
            padding: 18px 12px;
            border-radius: calc(var(--radius) + 2px);
            font-size: 15px;
            font-weight: 600;
            white-space: normal;
            text-align: center;
        }
        .categories.categories-only .cat-img { width: 62px; height: 62px; border-radius: 16px; }

        .back-cats {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 12px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .products {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding-bottom: 8px;
        }

        .product-card {
            position: relative;
            display: flex;
            flex-direction: column;
            text-align: left;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s, box-shadow 0.2s, border-color 0.2s;
        }
        .product-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); border-color: color-mix(in srgb, var(--primary) 30%, var(--border)); }
        .product-card:active { transform: scale(0.985); }

        .product-img, .product-placeholder {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: var(--surface-2);
        }

        .product-placeholder { display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 34px; }

        .product-card.no-image .product-placeholder {
            height: 64px;
            aspect-ratio: auto;
            font-size: 22px;
            color: color-mix(in srgb, var(--primary) 62%, var(--muted));
            background: linear-gradient(135deg,
                color-mix(in srgb, var(--primary) 12%, var(--surface-2)),
                color-mix(in srgb, var(--secondary) 16%, var(--surface-2)));
        }

        .product-body { display: flex; flex-direction: column; gap: 4px; padding: 10px 12px 14px; }
        .product-name { font-size: 14px; font-weight: 600; line-height: 1.35; min-height: 2.7em; padding-right: 28px; }
        .product-price { color: var(--primary); font-size: 15px; font-weight: 700; }

        .product-add {
            position: absolute;
            right: 10px;
            bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            font-size: 12px;
            box-shadow: 0 6px 14px color-mix(in srgb, var(--primary) 30%, transparent);
            pointer-events: none;
        }

        .empty-state {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 42px 20px;
            color: var(--muted);
            text-align: center;
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            background: var(--surface);
        }
        .empty-state i { font-size: 34px; opacity: 0.65; }
        .empty-state strong { color: var(--text); font-size: 16px; }

        .bottom-bar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 60;
            padding: 10px 14px calc(12px + env(safe-area-inset-bottom));
            background: linear-gradient(180deg, transparent, var(--bg) 34%);
            pointer-events: none;
        }

        .cart-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            width: min(1200px, 100%);
            margin: 0 auto;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow);
            pointer-events: auto;
            text-align: left;
        }

        .cart-bar-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            flex: 0 0 auto;
        }
        .cart-bar-copy { display: flex; flex-direction: column; flex: 1; min-width: 0; }
        .cart-bar-copy small { color: var(--muted); font-size: 11px; }
        .cart-bar-copy strong { font-size: 18px; }
        .cart-bar-action { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); font-size: 13px; font-weight: 700; white-space: nowrap; }

        .cart-overlay {
            position: fixed;
            inset: 0;
            z-index: 70;
            background: rgba(15, 23, 42, 0.55);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s;
        }
        .cart-overlay.open { opacity: 1; pointer-events: auto; }

        .cart-panel {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 80;
            display: flex;
            flex-direction: column;
            max-height: 88dvh;
            background: var(--surface);
            border-radius: 24px 24px 0 0;
            box-shadow: var(--shadow);
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .cart-panel.open { transform: translateY(0); }

        .cart-panel-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 16px 12px; border-bottom: 1px solid var(--border); }
        .cart-panel-header h2 { font-size: 19px; letter-spacing: -0.02em; }
        .cart-panel-header p { color: var(--muted); font-size: 12px; margin-top: 2px; }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--surface-2);
            color: var(--muted);
            font-size: 16px;
        }

        .cart-panel-body { flex: 1; min-height: 0; overflow-y: auto; padding: 4px 16px; }
        .cart-items { display: flex; flex-direction: column; }

        .cart-item { display: flex; gap: 10px; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .cart-item:last-child { border-bottom: 0; }
        .cart-item-image { width: 54px; height: 54px; flex: 0 0 auto; border-radius: 12px; object-fit: cover; background: var(--surface-2); }
        .cart-item-image.placeholder { display: flex; align-items: center; justify-content: center; color: var(--muted); }
        .cart-item-info { flex: 1; min-width: 0; }
        .cart-item-name { font-size: 14px; font-weight: 600; line-height: 1.3; }
        .cart-item-notes { color: var(--primary); font-size: 11px; margin-top: 3px; }
        .cart-item-aux { color: var(--muted); font-size: 11px; margin-top: 2px; }
        .cart-item-controls { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
        .qty-btn { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 1px solid var(--border); border-radius: 9px; background: var(--surface); color: var(--text); font-size: 16px; }
        .qty-btn:active { background: var(--surface-2); }
        .cart-item-qty { min-width: 22px; text-align: center; font-size: 14px; font-weight: 700; }
        .cart-item-end { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; gap: 8px; flex: 0 0 auto; }
        .cart-item-end strong { font-size: 14px; }
        .btn-remove { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 9px; color: var(--danger); background: color-mix(in srgb, var(--danger) 10%, transparent); }

        .cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 38px 16px; text-align: center; color: var(--muted); }
        .cart-empty i { font-size: 34px; opacity: 0.65; }

        .cart-panel-footer { padding: 14px 16px calc(16px + env(safe-area-inset-bottom)); border-top: 1px solid var(--border); background: var(--surface); }
        .cart-total { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
        .cart-total span { color: var(--muted); font-size: 13px; }
        .cart-total strong { font-size: 22px; }
        .cart-hint { color: var(--muted); font-size: 11px; margin-bottom: 10px; }

        .btn-confirm, .btn-primary, .btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 48px;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
        }
        .btn-confirm, .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 10px 22px color-mix(in srgb, var(--primary) 26%, transparent); }
        .btn-confirm:disabled { background: var(--surface-2); color: var(--muted); box-shadow: none; cursor: not-allowed; }
        .btn-ghost { background: var(--surface-2); color: var(--text); }

        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 90;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: rgba(15, 23, 42, 0.6);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s;
        }
        .modal-overlay.show { opacity: 1; pointer-events: auto; }

        .modal-sheet {
            position: relative;
            width: 100%;
            max-height: 92dvh;
            overflow-y: auto;
            padding: 20px 18px calc(20px + env(safe-area-inset-bottom));
            background: var(--surface);
            border-radius: 24px 24px 0 0;
            transform: translateY(24px);
            transition: transform 0.25s ease;
        }
        .modal-overlay.show .modal-sheet { transform: translateY(0); }

        .modal-close { position: absolute; top: 12px; right: 12px; z-index: 2; }
        .modal-image { width: 100%; aspect-ratio: 16 / 10; object-fit: cover; border-radius: 16px; margin-bottom: 14px; background: var(--surface-2); }
        .modal-head h3 { font-size: 21px; line-height: 1.2; letter-spacing: -0.02em; padding-right: 44px; }
        .modal-head p { color: var(--muted); font-size: 13px; margin-top: 4px; }
        .modal-price { color: var(--primary); font-size: 18px; font-weight: 700; margin-top: 4px; }

        .qty-control { display: flex; align-items: center; justify-content: center; gap: 16px; margin: 18px 0; }
        .qty-control button { display: flex; align-items: center; justify-content: center; width: 46px; height: 46px; border: 1px solid var(--border); border-radius: 14px; background: var(--surface-2); font-size: 22px; }
        .qty-control span { min-width: 42px; text-align: center; font-size: 26px; font-weight: 700; }

        .modal-textarea {
            width: 100%;
            min-height: 54px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            outline: none;
            resize: none;
            background: var(--surface-2);
            color: var(--text);
            font-size: 14px;
            margin-bottom: 12px;
        }
        .modal-textarea:focus { border-color: var(--primary); }

        .modal-aux { margin-bottom: 12px; }
        .modal-aux-title { color: var(--muted); font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .modal-aux-chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .modal-aux-chip { padding: 8px 12px; border: 1px solid var(--border); border-radius: 999px; background: var(--surface); color: var(--muted); font-size: 12px; cursor: pointer; transition: all 0.15s; }
        .modal-aux-chip.selected { background: var(--primary); border-color: var(--primary); color: #fff; }

        .modal-total { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 0; margin-top: 4px; border-top: 1px solid var(--border); }
        .modal-total span { color: var(--muted); font-size: 13px; }
        .modal-total strong { color: var(--primary); font-size: 21px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 12px; }

        .delivery-label { display: block; color: var(--muted); font-size: 12px; font-weight: 600; margin: 12px 0 5px; }
        .delivery-input { width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: 12px; outline: none; background: var(--surface-2); color: var(--text); font-size: 15px; }
        .delivery-input:focus { border-color: var(--primary); }
        .delivery-error { color: var(--danger); font-size: 12px; margin-top: 8px; }
        .kb-trigger { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 12px; background: var(--surface-2); color: var(--muted); font-size: 13px; }

        .keyboard-overlay {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100;
            display: none;
            padding: 8px 8px calc(8px + env(safe-area-inset-bottom));
            background: var(--surface);
            border-top: 1px solid var(--border);
            box-shadow: 0 -18px 40px rgba(15, 23, 42, 0.12);
        }
        .keyboard-overlay.show { display: block; }
        .kb-row { display: flex; justify-content: center; gap: 4px; margin-bottom: 4px; }
        .kb-row:last-child { margin-bottom: 0; }
        .kb-key { display: flex; align-items: center; justify-content: center; min-width: 32px; height: 44px; padding: 0 8px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface-2); color: var(--text); font-size: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08); }
        .kb-key:active { background: color-mix(in srgb, var(--primary) 12%, var(--surface-2)); }
        .kb-key-wide { min-width: 62px; }
        .kb-key-space { min-width: 180px; }
        .kb-key-action { background: var(--primary); border-color: var(--primary); color: #fff; min-width: 62px; }

        body.cart-open, body.modal-open { overflow: hidden; }

        @media (min-width: 600px) {
            .products { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
            .app-main { padding: 20px; }
            .hero { padding: 30px; }
        }

        @media (min-width: 900px) {
            .products { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .cart-panel { top: 0; left: auto; right: 0; bottom: 0; width: min(420px, 100%); max-height: none; border-radius: 24px 0 0 24px; transform: translateX(100%); }
            .cart-panel.open { transform: translateX(0); }
            .modal-overlay { align-items: center; padding: 24px; }
            .modal-sheet { width: min(540px, 100%); max-height: 90dvh; border-radius: 24px; padding: 24px; }
            .keyboard-overlay { width: min(680px, calc(100% - 24px)); left: 50%; right: auto; transform: translateX(-50%); border-radius: 18px 18px 0 0; }
            .hero h1 { max-width: 620px; }
            .bottom-bar { padding-left: 24px; padding-right: 24px; }
        }

        @media (min-width: 1200px) {
            .products { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        }

        @media (max-width: 420px) {
            .app-main { padding: 12px; }
            .hero { padding: 18px; border-radius: calc(var(--radius) + 2px); }
            .hero-mark { display: none; }
            .hero p { font-size: 12px; }
            .brand-name strong { font-size: 15px; }
            .product-name { font-size: 13px; }
            .product-body { padding: 8px 10px 12px; }
            .cart-bar-action { font-size: 12px; }
            .kb-key { height: 42px; min-width: 29px; font-size: 15px; }
            .kb-key-space { min-width: 120px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; scroll-behavior: auto !important; }
        }
    </style>
</head>
<body
    data-theme="{{ $web['template'] }}"
    style="--primary: {{ $web['primary_color'] }}; --secondary: {{ $web['secondary_color'] }}; --brand-font: '{{ $web['font'] }}', sans-serif;"
    class="{{ $categoriesOnly ? 'categories-only-mode' : '' }}"
>
    <header class="app-header">
        <div class="brand">
            @if($web['logoUrl'])
                <img src="{{ $web['logoUrl'] }}" alt="{{ $web['company_name'] }}" class="brand-logo" style="height: {{ $web['logo_height'] }}px;">
            @endif
            <div class="brand-name">
                <strong>{{ $web['company_name'] }}</strong>
                <span><i class="fas fa-bag-shopping"></i> Autopedido</span>
            </div>
        </div>
        <button type="button" class="header-cart" onclick="toggleCart()" aria-label="Ver carrito">
            <i class="fas fa-shopping-bag"></i>
            <span class="header-cart-count" id="cartHeaderCount">0</span>
        </button>
    </header>

    <main class="app-main">
        <section class="hero">
            <div class="hero-copy">
                <span class="hero-kicker">
                    <i class="fas {{ $deliveryType === 'web' ? 'fa-motorcycle' : 'fa-utensils' }}"></i>
                    {{ $deliveryType === 'web' ? 'Delivery' : 'Pide desde tu mesa' }}
                </span>
                <h1>¿Qué vas a pedir hoy?</h1>
                <p>Explora el menú, personaliza tu pedido y confirma. {{ $deliveryType === 'web' ? 'El pago se realiza vía Plin o Yape.' : 'El pago se realiza en caja.' }}</p>
            </div>
            <div class="hero-mark" aria-hidden="true"><i class="fas fa-bowl-food"></i></div>
        </section>

        <div class="search-wrap">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar producto..." autocomplete="off" oninput="applyFilters()">
                <button type="button" class="search-clear" id="searchClear" onclick="clearSearch()" aria-label="Limpiar búsqueda"><i class="fas fa-times"></i></button>
                <button type="button" class="search-kb" onclick="toggleKeyboard(document.getElementById('searchInput'))" aria-label="Teclado virtual"><i class="fas fa-keyboard"></i></button>
            </div>
        </div>

        <div class="categories {{ $categoriesOnly ? 'categories-only' : '' }}" id="categoriesContainer">
            <button type="button" class="cat-btn {{ $categoriesOnly ? '' : 'active' }}" data-cat="all" onclick="filterCategory('all')">
                <i class="fas fa-border-all"></i> Todos
            </button>
            @foreach($categories as $cat)
                <button type="button" class="cat-btn" data-cat="{{ $cat->id }}" onclick="filterCategory({{ $cat->id }})">
                    @if($showCategoryImages && $cat->imagen_url)
                        <img src="{{ $cat->imagen_url }}" class="cat-img" alt="{{ $cat->nombre }}" loading="lazy">
                    @endif
                    <span>{{ $cat->nombre }}</span>
                </button>
            @endforeach
        </div>

        <button type="button" id="backToCategories" class="back-cats" style="display:none;" onclick="backToCategories()">
            <i class="fas fa-arrow-left"></i> Volver a categorías
        </button>

        <section class="products" id="productsContainer" @if($categoriesOnly) style="display:none" @endif>
            @forelse($products as $p)
                @php($hasImage = $showProductImages && $p->imagen_url)
                <button
                    type="button"
                    class="product-card {{ $hasImage ? '' : 'no-image' }}"
                    data-id="{{ $p->id }}"
                    data-name="{{ $p->descripcion }}"
                    data-price="{{ $p->precio }}"
                    data-category="{{ $p->category_id ?? 0 }}"
                    @if($hasImage) data-image="{{ $p->imagen_url }}" @endif
                    onclick="openProductModal(this)"
                >
                    @if($hasImage)
                        <img src="{{ $p->imagen_url }}" class="product-img" alt="{{ $p->descripcion }}" loading="lazy">
                    @else
                        <span class="product-placeholder"><i class="fas fa-utensils"></i></span>
                    @endif
                    <span class="product-body">
                        <span class="product-name">{{ $p->descripcion }}</span>
                        <span class="product-price">S/ {{ number_format($p->precio, 2) }}</span>
                    </span>
                    <span class="product-add"><i class="fas fa-plus"></i></span>
                </button>
            @empty
                <div class="empty-state">
                    <i class="fas fa-bowl-food"></i>
                    <strong>No hay productos disponibles</strong>
                    <span>Vuelve a intentarlo en unos minutos.</span>
                </div>
            @endforelse
            <div class="empty-state" id="searchEmpty" style="display:none;">
                <i class="fas fa-magnifying-glass"></i>
                <strong>No encontramos ese producto</strong>
                <span>Prueba con otra palabra o cambia de categoría.</span>
            </div>
        </section>
    </main>

    <div class="bottom-bar">
        <button type="button" class="cart-bar" onclick="toggleCart()">
            <span class="cart-bar-badge" id="cartCount">0</span>
            <span class="cart-bar-copy">
                <small>Tu pedido</small>
                <strong id="cartTotal">S/ 0.00</strong>
            </span>
            <span class="cart-bar-action">Ver carrito <i class="fas fa-chevron-right"></i></span>
        </button>
    </div>

    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart(false)"></div>

    <aside class="cart-panel" id="cartPanel" aria-hidden="true" aria-label="Tu pedido">
        <div class="cart-panel-header">
            <div>
                <h2>Tu pedido</h2>
                <p id="cartPanelCount">0 productos</p>
            </div>
            <button type="button" class="icon-btn" onclick="toggleCart(false)" aria-label="Cerrar carrito"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-panel-body">
            <div id="cartItems" class="cart-items"></div>
        </div>
        <div class="cart-panel-footer">
            <div class="cart-total">
                <span>Total</span>
                <strong id="cartPanelTotal">S/ 0.00</strong>
            </div>
            <p class="cart-hint"><i class="fas fa-info-circle"></i> {{ $deliveryType === 'web' ? 'El pago se realiza vía Plin o Yape.' : 'El pago se realiza en caja.' }}</p>
            <button type="button" class="btn-confirm" id="btnConfirm" disabled onclick="confirmOrder()">
                <i class="fas fa-check"></i> Confirmar pedido
            </button>
        </div>
    </aside>

    <div class="modal-overlay" id="productModal" onclick="closeProductModal(event)">
        <div class="modal-sheet" onclick="event.stopPropagation()">
            <button type="button" class="icon-btn modal-close" onclick="closeProductModal()" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            <img id="modalProductImage" class="modal-image" alt="" style="display:none;">
            <div class="modal-head">
                <h3 id="modalProductName"></h3>
                <div class="modal-price" id="modalProductPrice"></div>
            </div>
            <div class="qty-control">
                <button type="button" onclick="modalQtyChange(-1)" aria-label="Quitar uno">−</button>
                <span id="modalQtyDisplay">1</span>
                <button type="button" onclick="modalQtyChange(1)" aria-label="Agregar uno">+</button>
            </div>
            <textarea class="modal-textarea" id="modalNotes" rows="2" placeholder="Nota para cocina (opcional)..." readonly onfocus="openKeyboard(this)"></textarea>
            <div class="modal-aux" id="modalAuxContainer" style="display:none;">
                <div class="modal-aux-title">Elementos auxiliares</div>
                <div class="modal-aux-chips" id="modalAuxChips"></div>
            </div>
            <div class="modal-total">
                <span>Total</span>
                <strong id="modalProductTotal">S/ 0.00</strong>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="closeProductModal()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="confirmAddToCart()"><i class="fas fa-plus"></i> Agregar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="deliveryModal" onclick="closeDeliveryModal(event)">
        <div class="modal-sheet" onclick="event.stopPropagation()">
            <button type="button" class="icon-btn modal-close" onclick="closeDeliveryModal()" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            <div class="modal-head">
                <h3><i class="fas fa-motorcycle"></i> Datos de delivery</h3>
                <p>Completa tus datos para el envío.</p>
            </div>
            <label class="delivery-label" for="deliveryName">Nombres y apellidos *</label>
            <input type="text" id="deliveryName" class="delivery-input" placeholder="Nombres y apellidos" onfocus="focusDeliveryInput(this)">
            <label class="delivery-label" for="deliveryAddress">Dirección de delivery *</label>
            <input type="text" id="deliveryAddress" class="delivery-input" placeholder="Dirección de entrega" onfocus="focusDeliveryInput(this)">
            <label class="delivery-label" for="deliveryPhone">Número de contacto *</label>
            <input type="tel" id="deliveryPhone" class="delivery-input" placeholder="Número de contacto" onfocus="focusDeliveryInput(this)">
            <div class="delivery-error" id="deliveryError" style="display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn-ghost" onclick="closeDeliveryModal()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="confirmDelivery()"><i class="fas fa-check"></i> Confirmar pedido</button>
            </div>
            <button type="button" class="kb-trigger" onclick="toggleKeyboard(activeInput || document.getElementById('deliveryName'))">
                <i class="fas fa-keyboard"></i> Teclado virtual
            </button>
        </div>
    </div>

    <div class="keyboard-overlay" id="keyboard">
        <div class="kb-row">
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
            <div class="kb-key kb-key-wide" onclick="pressKey(' ')">Espacio</div>
            <div class="kb-key kb-key-action" onclick="closeKeyboard()"><i class="fas fa-check"></i> OK</div>
        </div>
    </div>

    <script>
        let cart = [];
        const categoriesOnly = @json($categoriesOnly ?? false);
        let activeCategory = categoriesOnly ? null : 'all';

        const normalizeSearch = value => (value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
        let showCart = false;
        let modalProductId = null;
        let modalQty = 1;
        let modalPrice = 0;
        let modalImage = '';
        let activeInput = null;
        const companyId = {{ $companyId }};
        const autopedidoAuxiliaryItems = @json($auxiliaryItems);
        const auxMap = new Map((autopedidoAuxiliaryItems || []).map(item => [Number(item.id), item.name]));
        const kioskDeliveryType = @json($deliveryType);

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[character];
            });
        }

        function updateModalTotal() {
            const total = document.getElementById('modalProductTotal');
            if (total) {
                total.textContent = 'S/ ' + (modalPrice * modalQty).toFixed(2);
            }
        }

        function openProductModal(card) {
            modalProductId = Number(card.dataset.id);
            modalPrice = parseFloat(card.dataset.price || '0') || 0;
            modalImage = card.dataset.image || '';
            modalQty = 1;

            document.getElementById('modalProductName').textContent = card.dataset.name || '';
            document.getElementById('modalProductPrice').textContent = 'S/ ' + modalPrice.toFixed(2);
            document.getElementById('modalQtyDisplay').textContent = '1';
            document.getElementById('modalNotes').value = '';

            const image = document.getElementById('modalProductImage');
            if (modalImage) {
                image.src = modalImage;
                image.alt = card.dataset.name || '';
                image.style.display = 'block';
            } else {
                image.removeAttribute('src');
                image.alt = '';
                image.style.display = 'none';
            }

            loadModalAuxItems();
            updateModalTotal();
            document.getElementById('productModal').classList.add('show');
            document.body.classList.add('modal-open');
        }

        function loadModalAuxItems() {
            const container = document.getElementById('modalAuxChips');
            const wrapper = document.getElementById('modalAuxContainer');
            if (!container || !wrapper) return;

            container.innerHTML = '';
            wrapper.style.display = 'none';

            const items = autopedidoAuxiliaryItems || [];
            if (items.length === 0) return;

            items.forEach(item => {
                const chip = document.createElement('span');
                chip.className = 'modal-aux-chip';
                chip.dataset.id = item.id;
                chip.textContent = item.name;
                chip.onclick = function () {
                    this.classList.toggle('selected');
                };
                container.appendChild(chip);
            });

            wrapper.style.display = 'block';
        }

        function modalQtyChange(delta) {
            modalQty = Math.max(1, modalQty + delta);
            document.getElementById('modalQtyDisplay').textContent = modalQty;
            updateModalTotal();
        }

        function closeProductModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('productModal').classList.remove('show');
            document.body.classList.remove('modal-open');
            activeInput = null;
            closeKeyboard();
        }

        function confirmAddToCart() {
            const notes = document.getElementById('modalNotes').value.trim();
            const auxItems = [];
            document.querySelectorAll('#modalAuxChips .modal-aux-chip.selected').forEach(chip => {
                auxItems.push(parseInt(chip.dataset.id));
            });

            const existing = cart.find(item =>
                item.product_id === modalProductId
                && item.notes === notes
                && JSON.stringify(item.auxiliary_items) === JSON.stringify(auxItems)
            );

            if (existing) {
                existing.quantity += modalQty;
            } else {
                cart.push({
                    product_id: modalProductId,
                    name: document.getElementById('modalProductName').textContent,
                    price: modalPrice,
                    image: modalImage,
                    quantity: modalQty,
                    notes: notes,
                    auxiliary_items: auxItems
                });
            }

            closeProductModal();
            updateCart();
        }

        function updateCart() {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const totalText = 'S/ ' + total.toFixed(2);

            document.getElementById('cartCount').textContent = count;
            document.getElementById('cartHeaderCount').textContent = count;
            document.getElementById('cartTotal').textContent = totalText;
            document.getElementById('cartPanelTotal').textContent = totalText;
            document.getElementById('cartPanelCount').textContent = count + (count === 1 ? ' producto' : ' productos');
            document.getElementById('btnConfirm').disabled = count === 0;

            const container = document.getElementById('cartItems');
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <i class="fas fa-basket-shopping"></i>
                        <strong>Tu carrito está vacío</strong>
                        <span>Agrega productos para empezar tu pedido.</span>
                    </div>
                `;
                return;
            }

            container.innerHTML = cart.map((item, index) => {
                const notes = item.notes
                    ? `<div class="cart-item-notes">Nota: ${escapeHtml(item.notes)}</div>`
                    : '';
                const auxNames = (item.auxiliary_items || [])
                    .map(id => auxMap.get(Number(id)))
                    .filter(Boolean);
                const auxiliary = auxNames.length
                    ? `<div class="cart-item-aux">+ ${escapeHtml(auxNames.join(', '))}</div>`
                    : '';
                const image = item.image
                    ? `<img src="${escapeHtml(item.image)}" class="cart-item-image" alt="${escapeHtml(item.name)}">`
                    : `<div class="cart-item-image placeholder"><i class="fas fa-utensils"></i></div>`;

                return `
                    <div class="cart-item">
                        ${image}
                        <div class="cart-item-info">
                            <div class="cart-item-name">${escapeHtml(item.name)}</div>
                            ${notes}
                            ${auxiliary}
                            <div class="cart-item-controls">
                                <button type="button" class="qty-btn" onclick="changeQty(${index}, -1)" aria-label="Quitar uno">−</button>
                                <span class="cart-item-qty">${item.quantity}</span>
                                <button type="button" class="qty-btn" onclick="changeQty(${index}, 1)" aria-label="Agregar uno">+</button>
                            </div>
                        </div>
                        <div class="cart-item-end">
                            <strong>S/ ${(item.price * item.quantity).toFixed(2)}</strong>
                            <button type="button" class="btn-remove" onclick="removeItem(${index})" aria-label="Eliminar producto"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function changeQty(index, delta) {
            cart[index].quantity = Math.max(1, cart[index].quantity + delta);
            updateCart();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
        }

        function toggleCart(force) {
            const open = typeof force === 'boolean' ? force : !showCart;
            showCart = open;

            document.getElementById('cartPanel').classList.toggle('open', open);
            document.getElementById('cartOverlay').classList.toggle('open', open);
            document.getElementById('cartPanel').setAttribute('aria-hidden', open ? 'false' : 'true');
            document.body.classList.toggle('cart-open', open);
        }

        function filterCategory(categoryId) {
            activeCategory = categoryId;
            document.querySelectorAll('.cat-btn').forEach(button => {
                button.classList.toggle('active', button.dataset.cat == categoryId);
            });
            applyFilters();
        }

        function applyFilters() {
            const input = document.getElementById('searchInput');
            const query = normalizeSearch(input.value);
            const container = document.getElementById('productsContainer');
            const categories = document.getElementById('categoriesContainer');
            const backButton = document.getElementById('backToCategories');
            const clearButton = document.getElementById('searchClear');
            const empty = document.getElementById('searchEmpty');

            if (clearButton) {
                clearButton.style.display = query ? 'inline-flex' : 'none';
            }

            if (categoriesOnly) {
                const showProducts = query !== '' || activeCategory !== null;
                if (categories) categories.style.display = showProducts ? 'none' : '';
                if (container) container.style.display = showProducts ? '' : 'none';
                if (backButton) backButton.style.display = showProducts ? 'inline-flex' : 'none';
                if (!showProducts) return;
            } else if (container) {
                container.style.display = '';
            }

            let visible = 0;

            document.querySelectorAll('.product-card').forEach(card => {
                const categoryMatch = activeCategory === 'all'
                    || activeCategory === null
                    || card.dataset.category == activeCategory;
                const nameMatch = !query || normalizeSearch(card.dataset.name).includes(query);
                const show = categoryMatch && nameMatch;

                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (empty) {
                empty.style.display = visible === 0 ? 'flex' : 'none';
            }
        }

        function clearSearch() {
            const input = document.getElementById('searchInput');
            if (input) input.value = '';
            applyFilters();
        }

        function backToCategories() {
            activeCategory = null;
            const input = document.getElementById('searchInput');
            if (input) input.value = '';
            document.querySelectorAll('.cat-btn').forEach(button => button.classList.remove('active'));
            applyFilters();
        }

        function openKeyboard(input) {
            activeInput = input;
            document.getElementById('keyboard').classList.add('show');
            if (activeInput) activeInput.focus();
        }

        function toggleKeyboard(input) {
            if (document.getElementById('keyboard').classList.contains('show')) {
                closeKeyboard();
            } else {
                openKeyboard(input);
            }
        }

        function closeKeyboard() {
            activeInput = null;
            document.getElementById('keyboard').classList.remove('show');
        }

        function pressKey(key) {
            if (!activeInput) return;
            const start = activeInput.selectionStart;
            const end = activeInput.selectionEnd;
            activeInput.value = activeInput.value.substring(0, start) + key + activeInput.value.substring(end);
            activeInput.selectionStart = activeInput.selectionEnd = start + key.length;
            activeInput.focus();
            if (activeInput.id === 'searchInput') applyFilters();
        }

        function pressBackspace() {
            if (!activeInput) return;
            const start = activeInput.selectionStart;
            if (start === 0) return;
            activeInput.value = activeInput.value.substring(0, start - 1) + activeInput.value.substring(start);
            activeInput.selectionStart = activeInput.selectionEnd = start - 1;
            activeInput.focus();
            if (activeInput.id === 'searchInput') applyFilters();
        }

        function focusDeliveryInput(input) {
            activeInput = input;
        }

        function confirmOrder() {
            if (cart.length === 0) return;

            if (kioskDeliveryType === 'web') {
                document.getElementById('deliveryError').style.display = 'none';
                document.getElementById('deliveryModal').classList.add('show');
                document.body.classList.add('modal-open');
                return;
            }

            sendOrder({});
        }

        function closeDeliveryModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('deliveryModal').classList.remove('show');
            document.body.classList.remove('modal-open');
            activeInput = null;
            closeKeyboard();
        }

        function confirmDelivery() {
            const name = document.getElementById('deliveryName').value.trim();
            const address = document.getElementById('deliveryAddress').value.trim();
            const phone = document.getElementById('deliveryPhone').value.trim();
            const error = document.getElementById('deliveryError');

            if (!name || !address || !phone) {
                error.textContent = 'Complete todos los campos para el delivery.';
                error.style.display = 'block';
                return;
            }

            error.style.display = 'none';
            sendOrder({
                customer_name: name,
                delivery_address: address,
                contact_number: phone
            });
        }

        function resetConfirmButton() {
            const button = document.getElementById('btnConfirm');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-check"></i> Confirmar pedido';
        }

        function sendOrder(deliveryData) {
            const button = document.getElementById('btnConfirm');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            const payload = Object.assign({
                items: JSON.stringify(cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    notes: item.notes || null,
                    auxiliary_items: item.auxiliary_items && item.auxiliary_items.length > 0
                        ? item.auxiliary_items
                        : null
                })))
            }, deliveryData);

            fetch('/autopedido/confirm', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(async (response) => {
                const text = await response.text();
                let data;

                try {
                    data = JSON.parse(text);
                } catch (error) {
                    if (response.status === 419) {
                        alert('Sesión expirada. Recargando...');
                        location.reload();
                        return null;
                    }
                    throw new Error('Respuesta inválida');
                }

                if (!response.ok && data.errors) {
                    const first = Object.values(data.errors)[0];
                    alert(Array.isArray(first) ? first[0] : first);
                    return null;
                }

                return data;
            })
            .then(data => {
                if (!data) {
                    resetConfirmButton();
                    return;
                }

                if (data.success) {
                    window.location.href = '/autopedido/success/' + data.order_id;
                } else {
                    alert(data.message || 'Error al confirmar');
                    resetConfirmButton();
                }
            })
            .catch(() => {
                alert('Error de conexión');
                resetConfirmButton();
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            if (document.getElementById('keyboard').classList.contains('show')) {
                closeKeyboard();
                return;
            }
            if (document.getElementById('productModal').classList.contains('show')) {
                closeProductModal();
                return;
            }
            if (document.getElementById('deliveryModal').classList.contains('show')) {
                closeDeliveryModal();
                return;
            }
            if (showCart) {
                toggleCart(false);
            }
        });

        updateCart();
    </script>
</body>
</html>
