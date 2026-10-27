@if($web['show_autopedido'] ?? true)
<a href="/autopedido" class="virtual-order-cta" aria-label="Pedir en línea">
    <i class="fas fa-bag-shopping"></i> Pedir en línea
</a>
<style>
    .virtual-order-cta {
        position: fixed; left: 20px; bottom: 22px; z-index: 1040;
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--primary, #e94560); color: #fff;
        padding: 14px 20px; border-radius: 30px; font-weight: 700;
        text-decoration: none; box-shadow: 0 8px 22px rgba(0,0,0,.25);
        transition: transform .2s, filter .2s;
    }
    .virtual-order-cta:hover { color: #fff; transform: translateY(-2px); filter: brightness(.94); }
    @media (max-width: 576px) {
        .virtual-order-cta { padding: 12px 16px; font-size: 14px; left: 14px; bottom: 16px; }
    }
</style>
@endif
