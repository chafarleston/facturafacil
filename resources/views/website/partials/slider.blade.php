@if(!empty($slides) && ($web['slider_enabled'] ?? false))
<style>
    @media (max-width: 768px) {
        .web-slide-img {
            height: auto !important;
            object-fit: contain !important;
        }
        .web-slide-placeholder { height: 220px !important; }
        .web-slide-caption {
            position: static !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            padding: 14px 16px !important;
            background: #1a1a2e !important;
        }
        .web-slide-caption h3 { font-size: 1.1rem; }
        .web-slide-caption p { font-size: .85rem; margin-bottom: .5rem !important; }
        .web-slide-caption .btn { font-size: .8rem; padding: .35rem .7rem; }
    }
    @media (max-width: 576px) {
        .web-slide-img { height: auto !important; }
        .web-slide-placeholder { height: 200px !important; }
        .web-slide-caption { padding: 12px 14px !important; }
    }
</style>
<section class="py-4">
    <div class="container">
        <div id="webSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                @foreach($slides as $i => $slide)
                <button type="button" data-bs-target="#webSlider" data-bs-slide-to="{{ $i }}"
                        class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner" style="border-radius:18px; overflow:hidden;">
                @foreach($slides as $i => $slide)
                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                    @if($slide['image'])
                        <img src="{{ $slide['image'] }}" class="d-block w-100 web-slide-img"
                             style="height:440px; object-fit:cover;" alt="{{ $slide['title'] }}">
                    @else
                        <div class="web-slide-placeholder" style="height:440px; background:linear-gradient(135deg, var(--primary, #e94560), var(--secondary, #3a1c4a));"></div>
                    @endif
                    <div class="carousel-caption text-start web-slide-caption"
                         style="left:0; right:0; bottom:0; padding:30px; background:linear-gradient(0deg, rgba(0,0,0,.72), transparent);">
                        @if($slide['title'])<h3 class="fw-bold mb-2">{{ $slide['title'] }}</h3>@endif
                        @if($slide['text'])<p class="mb-2">{{ $slide['text'] }}</p>@endif
                        @if($web['show_autopedido'] ?? true)
                            <a href="/autopedido" class="btn btn-light btn-sm fw-semibold">
                                <i class="fas fa-bag-shopping me-1"></i> Pedir en línea
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @if(count($slides) > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#webSlider" data-bs-slide="prev" aria-label="Anterior">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#webSlider" data-bs-slide="next" aria-label="Siguiente">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
            @endif
        </div>
    </div>
</section>
@endif
