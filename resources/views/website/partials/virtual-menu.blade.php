@php
    $virtualMenus = $virtualMenus ?? [];
    $virtualSeconds = $virtualSeconds ?? [];
    $virtualTheme = $web['template'] ?? 'modern';
    $virtualPrimary = $web['primary_color'] ?: '#e94560';
    $virtualSecondary = $web['secondary_color'] ?: '#3a1c4a';
    $virtualFont = $web['font'] ?: 'Poppins';
@endphp

@if(count($virtualMenus) || count($virtualSeconds))
<section id="menu-virtual" class="vm-section" data-vm-theme="{{ $virtualTheme }}" style="--vm-primary: {{ $virtualPrimary }}; --vm-secondary: {{ $virtualSecondary }}; --vm-font: '{{ $virtualFont }}', sans-serif;">
    <style>
        #menu-virtual.vm-section {
            --vm-bg: #fafafc;
            --vm-surface: #ffffff;
            --vm-surface-2: #f7f8fa;
            --vm-text: #1d1e2c;
            --vm-muted: #6b7280;
            --vm-border: #e5e7eb;
            --vm-radius: 18px;
            --vm-radius-sm: 10px;
            --vm-shadow: 0 10px 30px rgba(20, 25, 40, .06);
            position: relative;
            padding: 70px 0;
            background: var(--vm-bg);
            color: var(--vm-text);
            font-family: var(--vm-font);
            scroll-margin-top: 80px;
        }

        .vm-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--vm-primary), var(--vm-secondary));
        }

        .vm-section[data-vm-theme="elegant"] {
            --vm-bg: #f8f5ef;
            --vm-surface-2: #faf7f1;
            --vm-text: #241f19;
            --vm-muted: #7b7164;
            --vm-border: #e7dfd2;
            --vm-radius: 3px;
            --vm-radius-sm: 2px;
            --vm-shadow: 0 8px 20px rgba(60, 45, 20, .05);
        }

        .vm-section[data-vm-theme="minimal"] {
            --vm-bg: #ffffff;
            --vm-surface-2: #fafafa;
            --vm-text: #111111;
            --vm-muted: #666666;
            --vm-border: #e5e5e5;
            --vm-radius: 0;
            --vm-radius-sm: 0;
            --vm-shadow: none;
        }

        .vm-section[data-vm-theme="classic"] {
            --vm-bg: #f3ede2;
            --vm-surface-2: #fbf7f0;
            --vm-text: #2f2a22;
            --vm-muted: #7b6f5e;
            --vm-border: #d8cdbc;
            --vm-radius: 0;
            --vm-radius-sm: 0;
            --vm-shadow: 0 8px 20px rgba(60, 45, 20, .06);
        }

        .vm-section[data-vm-theme="dark"] {
            --vm-bg: #0b0d12;
            --vm-surface: #151923;
            --vm-surface-2: #11151e;
            --vm-text: #e9e9f2;
            --vm-muted: #9aa3b2;
            --vm-border: #242a38;
            --vm-radius: 18px;
            --vm-radius-sm: 12px;
            --vm-shadow: 0 14px 36px rgba(0, 0, 0, .28);
        }

        .vm-section[data-vm-theme="tropical"] {
            --vm-bg: #fffaf2;
            --vm-surface-2: #fff4e6;
            --vm-text: #12332e;
            --vm-muted: #6b7f7a;
            --vm-border: #f0e2cc;
            --vm-radius: 24px;
            --vm-radius-sm: 16px;
            --vm-shadow: 0 10px 26px rgba(255, 122, 89, .10);
        }

        .vm-head {
            max-width: 680px;
            margin: 0 auto 28px;
            text-align: center;
        }

        .vm-eyebrow {
            margin-bottom: 6px;
            color: var(--vm-primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .vm-title {
            margin: 0;
            color: var(--vm-text);
            font-size: clamp(1.7rem, 3vw, 2.5rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.02em;
        }

        .vm-subtitle {
            max-width: 560px;
            margin: 10px auto 0;
            color: var(--vm-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .vm-subhead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 26px 0 12px;
        }

        .vm-subhead:first-of-type {
            margin-top: 0;
        }

        .vm-subhead h3 {
            margin: 0;
            color: var(--vm-text);
            font-size: 18px;
            font-weight: 800;
        }

        .vm-subhead span {
            flex-shrink: 0;
            padding: 5px 10px;
            border: 1px solid var(--vm-border);
            border-radius: 999px;
            background: var(--vm-surface);
            color: var(--vm-muted);
            font-size: 11px;
            font-weight: 600;
        }

        .vm-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .vm-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--vm-border);
            border-radius: var(--vm-radius);
            background: var(--vm-surface);
            box-shadow: var(--vm-shadow);
        }

        .vm-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--vm-border);
            background: var(--vm-surface-2);
        }

        .vm-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--vm-primary);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .vm-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
        }

        .vm-item {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .vm-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            border: 1px solid var(--vm-border);
            border-radius: var(--vm-radius-sm);
            background: var(--vm-surface-2);
            color: var(--vm-primary);
            font-size: 18px;
        }

        .vm-item-body { min-width: 0; }

        .vm-role {
            display: block;
            margin-bottom: 2px;
            color: var(--vm-primary);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .vm-name {
            color: var(--vm-text);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .vm-card-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 16px;
            border-top: 1px solid var(--vm-border);
            background: var(--vm-surface-2);
        }

        .vm-card-foot span {
            color: var(--vm-muted);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .vm-card-foot strong {
            color: var(--vm-primary);
            font-size: 19px;
            font-weight: 800;
            white-space: nowrap;
        }

        @media (min-width: 768px) {
            .vm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 1080px) {
            .vm-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 575px) {
            #menu-virtual.vm-section { padding: 50px 0; }
            .vm-title { font-size: 1.7rem; }
            .vm-subhead h3 { font-size: 16px; }
            .vm-card-head,
            .vm-card-foot { padding-left: 14px; padding-right: 14px; }
            .vm-card-body { padding: 14px; }
            .vm-icon { width: 42px; height: 42px; font-size: 16px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>

    <div class="container">
        <div class="vm-head">
            <div class="vm-eyebrow">Menú Virtual</div>
            <h2 class="vm-title">Nuestros menús</h2>
            <p class="vm-subtitle">Consulta nuestros menús y segundos disponibles.</p>
        </div>

        @if(count($virtualMenus))
            <div class="vm-subhead">
                <h3>Menús</h3>
                <span>{{ count($virtualMenus) }} {{ count($virtualMenus) === 1 ? 'opción' : 'opciones' }}</span>
            </div>
            <div class="vm-grid">
                @foreach($virtualMenus as $menu)
                    <article class="vm-card">
                        <div class="vm-card-head">
                            <span class="vm-number">Menú {{ $menu['number'] }}</span>
                        </div>
                        <div class="vm-card-body">
                            <div class="vm-item">
                                <span class="vm-icon"><i class="fas fa-utensils"></i></span>
                                <div class="vm-item-body">
                                    <span class="vm-role">Menú</span>
                                    <div class="vm-name">{{ $menu['main_name'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="vm-card-foot">
                            <span>Precio del menú</span>
                            <strong>S/ {{ number_format($menu['price'], 2) }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if(count($virtualSeconds))
            <div class="vm-subhead">
                <h3>Segundos</h3>
                <span>{{ count($virtualSeconds) }} {{ count($virtualSeconds) === 1 ? 'opción' : 'opciones' }}</span>
            </div>
            <div class="vm-grid">
                @foreach($virtualSeconds as $second)
                    <article class="vm-card">
                        <div class="vm-card-head">
                            <span class="vm-number">Segundo {{ $second['number'] }}</span>
                        </div>
                        <div class="vm-card-body">
                            <div class="vm-item">
                                <span class="vm-icon"><i class="fas fa-bowl-rice"></i></span>
                                <div class="vm-item-body">
                                    <span class="vm-role">Segundo</span>
                                    <div class="vm-name">{{ $second['name'] }}</div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
