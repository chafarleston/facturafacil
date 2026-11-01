<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Virtual · {{ $web['company_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $web['fontUrl'] }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @include('partials.web-theme-vars')

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            padding: 18px;
            font-family: var(--font);
            color: var(--text);
            background: var(--body-image), var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        .menu-page {
            width: min(1120px, 100%);
            margin: 0 auto;
        }

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 18px;
            padding: 30px 24px 26px;
            border: var(--card-border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
        }

        .brand img {
            max-width: 220px;
            width: auto;
            object-fit: contain;
        }

        .company {
            color: var(--text);
            font-size: 21px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: .02em;
            text-transform: var(--brand-transform);
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 4px 12px;
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
            text-align: center;
        }

        .meta span { display: inline-flex; align-items: center; gap: 4px; }
        .meta i { color: var(--primary); }

        .kicker {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .hero h1 {
            margin-top: 6px;
            color: var(--text);
            font-size: clamp(1.9rem, 4.5vw, 3rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -.02em;
        }

        .hero p {
            max-width: 620px;
            margin: 10px auto 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 22px 2px 12px;
        }

        .section-head h2 {
            color: var(--text);
            font-size: 18px;
            font-weight: 800;
        }

        .section-head span {
            flex-shrink: 0;
            padding: 5px 10px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .combo {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: var(--card-border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow-sm);
        }

        .combo-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface-2);
        }

        .combo-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .combo-label {
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: right;
        }

        .combo-products {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
        }

        .product {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .product-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface-2);
            color: var(--primary);
            font-size: 18px;
        }

        .product-body { min-width: 0; }

        .product-role {
            display: block;
            margin-bottom: 2px;
            color: var(--primary);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .product-name {
            color: var(--text);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .combo-sep {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .combo-sep::before,
        .combo-sep::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .no-second {
            padding: 10px 12px;
            border: 1px dashed var(--border);
            border-radius: var(--radius-sm);
            color: var(--muted);
            font-size: 11px;
            font-style: italic;
            line-height: 1.5;
        }

        .combo-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 16px;
            border-top: 1px solid var(--border);
            background: var(--surface-2);
        }

        .combo-footer span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .combo-footer strong {
            color: var(--grand-color);
            font-size: 19px;
            font-weight: 800;
            white-space: nowrap;
        }

        .empty {
            padding: 46px 24px;
            border: var(--card-border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .empty i {
            display: block;
            margin-bottom: 12px;
            color: var(--muted);
            opacity: .5;
            font-size: 42px;
        }

        .empty h2 { margin-bottom: 6px; font-size: 19px; }
        .empty p { color: var(--muted); font-size: 13px; }

        .footer {
            margin-top: 22px;
            padding: 18px 4px 8px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
        }

        .footer strong { color: var(--text); }

        @media (min-width: 760px) {
            .menu-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hero { padding: 34px 30px 30px; }
        }

        @media (min-width: 1080px) {
            .menu-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 520px) {
            body { padding: 10px; }
            .hero { padding: 24px 16px 22px; }
            .company { font-size: 18px; }
            .section-head h2 { font-size: 16px; }
            .product-icon { width: 42px; height: 42px; font-size: 16px; }
            .combo-products { padding: 14px; }
            .combo-head, .combo-footer { padding-left: 14px; padding-right: 14px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body data-theme="{{ $web['template'] }}">
    <div class="menu-page">
        <header class="hero">
            <div class="brand">
                @if($web['logoUrl'])
                    <img src="{{ $web['logoUrl'] }}" alt="{{ $web['company_name'] }}" style="height: {{ $web['logo_height'] }}px;">
                @endif
                <div>
                    <div class="company">{{ $web['company_name'] }}</div>
                    <div class="meta">
                        @if($company?->ruc)<span><i class="fas fa-id-card"></i> RUC: {{ $company->ruc }}</span>@endif
                        @if($company?->direccion)<span><i class="fas fa-location-dot"></i> {{ $company->direccion }}</span>@endif
                        @if($company?->telefono)<span><i class="fas fa-phone"></i> {{ $company->telefono }}</span>@endif
                        @if($company?->email)<span><i class="fas fa-envelope"></i> {{ $company->email }}</span>@endif
                    </div>
                </div>
            </div>
            <span class="kicker">Menú Virtual</span>
            <h1>Nuestros menús</h1>
            <p>Consulta nuestros menús y segundos disponibles.</p>
        </header>

        @if(count($menus))
            <div class="section-head">
                <h2>Menús</h2>
                <span>{{ count($menus) }} {{ count($menus) === 1 ? 'opción' : 'opciones' }}</span>
            </div>

            <div class="menu-grid">
                @foreach($menus as $menu)
                    <article class="combo">
                        <div class="combo-head">
                            <span class="combo-number">Menú {{ $menu['number'] }}</span>
                        </div>

                        <div class="combo-products">
                            <div class="product">
                                <span class="product-icon"><i class="fas fa-utensils"></i></span>
                                <div class="product-body">
                                    <span class="product-role">Menú</span>
                                    <div class="product-name">{{ $menu['main_name'] }}</div>
                                </div>
                            </div>

                        </div>

                        <div class="combo-footer">
                            <span>Precio del menú</span>
                            <strong>S/ {{ number_format($menu['price'], 2) }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty">
                <i class="fas fa-book-open"></i>
                <h2>Menú virtual en preparación</h2>
                <p>Pronto publicaremos nuestras combinaciones.</p>
            </div>
        @endif

        @if(count($seconds))
            <div class="section-head">
                <h2>Segundos</h2>
                <span>{{ count($seconds) }} {{ count($seconds) === 1 ? 'opción' : 'opciones' }}</span>
            </div>

            <div class="menu-grid">
                @foreach($seconds as $second)
                    <article class="combo">
                        <div class="combo-head">
                            <span class="combo-number">Segundo {{ $second['number'] }}</span>
                        </div>
                        <div class="combo-products">
                            <div class="product">
                                <span class="product-icon"><i class="fas fa-bowl-rice"></i></span>
                                <div class="product-body">
                                    <span class="product-role">Segundo</span>
                                    <div class="product-name">{{ $second['name'] }}</div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <footer class="footer">
            <strong>{{ $web['company_name'] }}</strong><br>
            Menú Virtual · Precios sujetos a cambios.
        </footer>
    </div>
</body>
</html>
