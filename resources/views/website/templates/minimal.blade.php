<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $web['hero_title'] ?: ($company->nombre_comercial ?: $company->razon_social) }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($web['hero_description']), 160) }}">

    @php
        $font = $web['font'] ?: 'Poppins';
        $fontUrl = 'https://fonts.googleapis.com/css2?family=' . urlencode($font) . ':wght@400;500;600;700&display=swap';
        $waNumber = preg_replace('/\D+/', '', $web['whatsapp'] ?? '');
        $brandName = $company->nombre_comercial ?: $company->razon_social;
    @endphp

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ $fontUrl }}" rel="stylesheet">

    <style>
        :root { --primary: {{ $web['primary_color'] ?: '#111111' }}; --secondary: {{ $web['secondary_color'] ?: '#555555' }}; --ink: #111; --muted: #777; }
        * { font-family: '{{ $font }}', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background: #fff; color: #222; overflow-x: hidden; }
        section { scroll-margin-top: 90px; }

        .navbar { background: #fff; border-bottom: 1px solid #eee; }
        .navbar-brand img { height: 40px; width: auto; margin-right: 12px; }
        .navbar-brand span { font-weight: 600; letter-spacing: .5px; color: var(--ink); }
        .navbar .nav-link { color: #666; font-size: .9rem; }
        .navbar .nav-link:hover { color: var(--ink); }

        .btn-minimal { background: var(--ink); border: 1px solid var(--ink); color: #fff; border-radius: 0; padding: 12px 26px; font-size: .85rem; font-weight: 500; }
        .btn-minimal:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-outline-minimal { border: 1px solid #ccc; color: var(--ink); border-radius: 0; padding: 11px 24px; font-size: .85rem; font-weight: 500; }
        .btn-outline-minimal:hover { border-color: var(--ink); background: var(--ink); color: #fff; }

        .eyebrow { font-size: .72rem; letter-spacing: 3px; text-transform: uppercase; color: var(--muted); }
        .section-title { font-weight: 600; color: var(--ink); font-size: clamp(1.6rem, 3vw, 2.3rem); letter-spacing: -.5px; }
        .muted { color: var(--muted); }

        .hero { padding: 110px 0 90px; }
        .hero h1 { font-weight: 600; font-size: clamp(2.4rem, 6vw, 4.6rem); line-height: 1.02; letter-spacing: -1.5px; color: var(--ink); }
        .hero .lead { color: #666; font-size: 1.1rem; max-width: 560px; }
        .hero-img { width: 100%; height: 460px; object-fit: cover; filter: grayscale(15%); }
        .hero-panel { width: 100%; height: 460px; background: #f4f4f4; display: flex; align-items: center; justify-content: center; color: #ccc; }
        .hero-panel i { font-size: 5rem; }
        .stat { border-top: 1px solid #eee; padding-top: 14px; }
        .stat .num { font-size: 1.6rem; font-weight: 600; color: var(--ink); }

        .feature { padding: 8px 0; }
        .feature i { color: var(--primary); font-size: 1.2rem; }
        .feature h5 { font-weight: 600; font-size: 1rem; color: var(--ink); margin: 10px 0 6px; }

        .about-img { width: 100%; height: 520px; object-fit: cover; filter: grayscale(10%); }

        .filter-btn { border: none; background: transparent; color: #777; padding: 6px 2px; margin: 0 16px 8px 0; font-size: .88rem; border-bottom: 1px solid transparent; border-radius: 0; }
        .filter-btn.active, .filter-btn:hover { color: var(--ink); border-bottom-color: var(--ink); }

        .product-card { border: 1px solid #eee; border-radius: 0; background: #fff; height: 100%; transition: border-color .2s; }
        .product-card:hover { border-color: var(--ink); }
        .product-img { width: 100%; height: 220px; object-fit: cover; background: #f6f6f6; }
        .product-img-placeholder { width: 100%; height: 220px; display: flex; align-items: center; justify-content: center; background: #f6f6f6; color: #ccc; font-size: 2.6rem; }
        .product-cat { font-size: .7rem; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); }
        .product-price { color: var(--ink); font-weight: 600; }

        .gallery-item { overflow: hidden; cursor: pointer; position: relative; }
        .gallery-item img { width: 100%; height: 230px; object-fit: cover; transition: opacity .3s; }
        .gallery-item:hover img { opacity: .85; }
        .gallery-item::after { content: '\f00e'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; background: rgba(0,0,0,.35); opacity: 0; transition: opacity .25s; }
        .gallery-item:hover::after { opacity: 1; }

        .contact-line { border-top: 1px solid #eee; padding: 14px 0; display: flex; align-items: center; }
        .contact-line i { color: var(--muted); width: 26px; }
        .contact-line a { color: #222; text-decoration: none; }
        .contact-line a:hover { color: var(--primary); }
        .map-wrap { min-height: 340px; border: 1px solid #eee; }
        .map-wrap iframe { width: 100%; height: 100%; min-height: 340px; border: 0; }

        footer { border-top: 1px solid #eee; color: var(--muted); }
        footer a { color: var(--muted); text-decoration: none; }
        footer a:hover { color: var(--ink); }
        .social-link { width: 36px; height: 36px; border: 1px solid #ddd; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; }

        .wa-float { position: fixed; right: 20px; bottom: 22px; z-index: 1040; width: 54px; height: 54px; border-radius: 50%; background: #25d366; color: #fff; font-size: 27px; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 18px rgba(0,0,0,.22); }
        .wa-float:hover { color: #fff; }
        .to-top { position: fixed; right: 24px; bottom: 88px; z-index: 1039; width: 40px; height: 40px; border-radius: 50%; border: 1px solid #ddd; background: #fff; color: var(--ink); display: none; }
        .to-top.show { display: block; }

        .reveal { opacity: 0; transform: translateY(22px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        @media (max-width: 991px) {
            .hero { padding: 70px 0 50px; }
            .hero-img, .hero-panel, .about-img { height: 340px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top py-3" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#inicio">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $brandName }}" style="height: {{ $web['logo_height'] ?? 56 }}px; width:auto;">@endif
            <span>{{ $brandName }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Menú"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                @if($web['about_text'] || $web['about_image'])<li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>@endif
                @if($web['show_catalog'] && $products->count())<li class="nav-item"><a class="nav-link" href="#catalogo">Catálogo</a></li>@endif
                @if($gallery->count())<li class="nav-item"><a class="nav-link" href="#galeria">Galería</a></li>@endif
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
            </ul>
            <div class="d-flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-minimal"><i class="fas fa-gauge-high me-1"></i> Ir al sistema</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-minimal"><i class="fas fa-right-to-bracket me-1"></i> Iniciar sesión</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<section class="hero" id="inicio">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                @if($web['hero_subtitle'])<div class="eyebrow mb-3">{{ $web['hero_subtitle'] }}</div>@endif
                <h1 class="mb-4">{{ $web['hero_title'] }}</h1>
                @if($web['hero_description'])<p class="lead mb-4">{{ $web['hero_description'] }}</p>@endif
                <div class="d-flex flex-wrap gap-2">
                    @if($web['show_catalog'] && $products->count())<a href="#catalogo" class="btn btn-minimal">Ver catálogo</a>@endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-outline-minimal"><i class="fab fa-whatsapp me-1"></i> WhatsApp</a>@endif
                </div>
                @if($products->count() || $categories->count())
                <div class="row g-4 mt-5">
                    <div class="col-4"><div class="stat"><div class="num">{{ $products->count() }}</div><div class="eyebrow">Productos</div></div></div>
                    <div class="col-4"><div class="stat"><div class="num">{{ $categories->count() }}</div><div class="eyebrow">Categorías</div></div></div>
                    <div class="col-4"><div class="stat"><div class="num"><i class="fas fa-star"></i></div><div class="eyebrow">Calidad</div></div></div>
                </div>
                @endif
            </div>
            <div class="col-lg-6 reveal">
                @if($web['hero_image'])
                    <img src="{{ asset('storage/' . $web['hero_image']) }}" class="hero-img" alt="{{ $web['hero_title'] }}">
                @else
                    <div class="hero-panel"><i class="fas fa-utensils"></i></div>
                @endif
            </div>
        </div>
    </div>
</section>

@include('website.partials.slider')

@if($web['feature_1_title'] || $web['feature_2_title'] || $web['feature_3_title'])
<section class="py-5" id="beneficios">
    <div class="container py-4">
        <div class="row mb-5 reveal">
            <div class="col-lg-5"><div class="eyebrow mb-2">Beneficios</div><h2 class="section-title">{{ $web['features_title'] }}</h2></div>
        </div>
        <div class="row g-4">
            @foreach([[1, 'fa-award'], [2, 'fa-hand-holding-heart'], [3, 'fa-motorcycle']] as [$i, $icon])
            @if($web['feature_'.$i.'_title'] || $web['feature_'.$i.'_text'])
            <div class="col-md-4 reveal">
                <div class="feature">
                    <i class="fas {{ $icon }}"></i>
                    <h5>{{ $web['feature_'.$i.'_title'] }}</h5>
                    <p class="muted mb-0 small">{{ $web['feature_'.$i.'_text'] }}</p>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif

@if($web['about_text'] || $web['about_image'])
<section class="py-5" id="nosotros" style="background:#fafafa;">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="{{ $web['about_image'] ? 'col-lg-6' : 'col-lg-8 mx-auto' }} reveal">
                <div class="eyebrow mb-2">Conócenos</div>
                <h2 class="section-title mb-3">{{ $web['about_title'] }}</h2>
                <div style="white-space: pre-line; color:#666; line-height:1.9;">{{ $web['about_text'] }}</div>
            </div>
            @if($web['about_image'])
            <div class="col-lg-6 reveal">
                <img src="{{ asset('storage/' . $web['about_image']) }}" class="about-img" alt="{{ $web['about_title'] }}">
            </div>
            @endif
        </div>
    </div>
</section>
@endif

@if($web['show_catalog'] && $products->count())
<section class="py-5" id="catalogo">
    <div class="container py-4">
        <div class="row mb-4 reveal">
            <div class="col-lg-6"><div class="eyebrow mb-2">Nuestro menú</div><h2 class="section-title">{{ $web['catalog_title'] }}</h2></div>
            <div class="col-lg-6 d-flex align-items-end justify-content-lg-end mt-3 mt-lg-0">
                <div class="input-group" style="max-width:320px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="catalogSearch" class="form-control border-start-0" placeholder="Buscar producto...">
                </div>
            </div>
        </div>
        @if($categories->count())
        <div class="mb-4 reveal">
            <button type="button" class="filter-btn active" data-filter="all">Todos</button>
            @foreach($categories as $cat)<button type="button" class="filter-btn" data-filter="{{ $cat->id }}">{{ $cat->nombre }}</button>@endforeach
        </div>
        @endif
        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-6 col-md-4 col-lg-3 product-item reveal" data-category="{{ $product->category_id ?? 0 }}" data-name="{{ mb_strtolower($product->descripcion) }}">
                <div class="product-card d-flex flex-column">
                    @if($product->imagen_url)
                        <img src="{{ $product->imagen_url }}" class="product-img" alt="{{ $product->descripcion }}" loading="lazy">
                    @else
                        <div class="product-img-placeholder"><i class="fas fa-utensils"></i></div>
                    @endif
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        @if($product->category_id && isset($categoryNames[$product->category_id]))<div class="product-cat mb-1">{{ $categoryNames[$product->category_id] }}</div>@endif
                        <h6 class="mb-1 fw-normal">{{ $product->descripcion }}</h6>
                        @if($web['show_prices'])<div class="product-price mt-1">S/ {{ number_format($product->precio, 2) }}</div>@endif
                        @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, quiero pedir: ' . $product->descripcion) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-minimal mt-3"><i class="fab fa-whatsapp me-1"></i> Pedir</a>@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div id="catalogEmpty" class="text-center muted py-4" style="display:none;"><i class="fas fa-search fa-2x mb-2 d-block"></i> No se encontraron productos.</div>
    </div>
</section>
@endif

@if($gallery->count())
<section class="py-5" id="galeria" style="background:#fafafa;">
    <div class="container py-4">
        <div class="row mb-4 reveal"><div class="col-lg-6"><div class="eyebrow mb-2">Nuestro trabajo</div><h2 class="section-title">{{ $web['gallery_title'] }}</h2></div></div>
        <div class="row g-3">
            @foreach($gallery as $gp)
            <div class="col-6 col-md-3 reveal"><div class="gallery-item" data-img="{{ $gp->imagen_url }}"><img src="{{ $gp->imagen_url }}" alt="{{ $gp->descripcion }}" loading="lazy"></div></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($web['address'] || $web['phone'] || $web['email'] || $web['facebook'] || $web['instagram'] || $waNumber)
<section class="py-5" id="contacto">
    <div class="container py-4">
        <div class="row mb-5 reveal"><div class="col-lg-6"><div class="eyebrow mb-2">Estamos para ti</div><h2 class="section-title">Contáctanos</h2></div></div>
        <div class="row g-5">
            <div class="col-lg-5 reveal">
                @if($web['address'])<div class="contact-line"><i class="fas fa-location-dot"></i><span>{{ $web['address'] }}</span></div>@endif
                @if($web['phone'])<div class="contact-line"><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $web['phone']) }}">{{ $web['phone'] }}</a></div>@endif
                @if($web['email'])<div class="contact-line"><i class="fas fa-envelope"></i><a href="mailto:{{ $web['email'] }}">{{ $web['email'] }}</a></div>@endif
                @if($waNumber)<div class="contact-line"><i class="fab fa-whatsapp"></i><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener">WhatsApp</a></div>@endif
                <div class="d-flex gap-2 mt-4">
                    @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-facebook-f"></i></a>@endif
                    @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-instagram"></i></a>@endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-whatsapp"></i></a>@endif
                </div>
                @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-minimal mt-4"><i class="fab fa-whatsapp me-1"></i> Pedir por WhatsApp</a>@endif
            </div>
            <div class="col-lg-7 reveal">
                <div class="map-wrap h-100">
                    @if($web['address'])
                        <iframe src="https://www.google.com/maps?q={{ urlencode($web['address']) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación"></iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="background:#f6f6f6; color:#bbb;"><div class="text-center"><i class="fas fa-map-location-dot fa-3x mb-2 d-block"></i><span>Configura tu dirección</span></div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<footer class="py-4 mt-3">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small">
            <span>&copy; {{ date('Y') }} {{ $brandName }}. {{ $web['footer_text'] }}</span>
            <div class="d-flex gap-3">
                @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener">Facebook</a>@endif
                @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener">Instagram</a>@endif
                <a href="{{ route('login') }}"><i class="fas fa-lock me-1"></i> Acceso al sistema</a>
            </div>
        </div>
    </div>
</footer>

@if($gallery->count())
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <img id="galleryModalImg" src="" alt="Galería" class="w-100">
            </div>
        </div>
    </div>
</div>
@endif

@if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" class="wa-float" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>@endif
<button type="button" class="to-top" id="toTop" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></button>

@include('website.partials.virtual-order-cta')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
@include('website.partials.scripts')
</script>
</body>
</html>
