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
        :root { --primary: {{ $web['primary_color'] ?: '#7c5cff' }}; --secondary: {{ $web['secondary_color'] ?: '#00d4ff' }}; --bg: #0b0d12; --panel: #151923; --line: #242a38; --text: #e9e9f2; --muted: #9aa3b2; }
        * { font-family: '{{ $font }}', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background: var(--bg); color: var(--text); overflow-x: hidden; }
        section { scroll-margin-top: 86px; }

        .navbar { background: rgba(11,13,18,.92); backdrop-filter: blur(10px); border-bottom: 1px solid var(--line); }
        .navbar-brand img { height: 44px; width: auto; margin-right: 10px; }
        .navbar-brand span { color: #fff; font-weight: 700; }
        .navbar .nav-link { color: var(--muted); }
        .navbar .nav-link:hover { color: #fff; }

        .btn-dark-theme { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; color: #fff; font-weight: 600; border-radius: 30px; padding: 12px 26px; }
        .btn-dark-theme:hover { color: #fff; filter: brightness(1.08); }
        .btn-outline-dark-theme { border: 1px solid var(--line); color: var(--text); border-radius: 30px; padding: 11px 24px; font-weight: 600; }
        .btn-outline-dark-theme:hover { border-color: var(--primary); color: #fff; }

        .eyebrow { font-size: .74rem; letter-spacing: 3px; text-transform: uppercase; color: var(--secondary); font-weight: 700; }
        .section-title { font-weight: 800; color: #fff; font-size: clamp(1.7rem, 3.4vw, 2.6rem); }
        .muted { color: var(--muted); }

        .hero { position: relative; min-height: 86vh; display: flex; align-items: center; overflow: hidden; background: radial-gradient(circle at 80% 20%, rgba(124,92,255,.25), transparent 45%), radial-gradient(circle at 20% 80%, rgba(0,212,255,.18), transparent 45%); }
        .hero::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent, var(--bg)); }
        .hero .container { position: relative; z-index: 1; }
        .hero h1 { font-weight: 800; font-size: clamp(2.2rem, 5.4vw, 4.2rem); line-height: 1.05; background: linear-gradient(90deg, #fff, var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero .lead { color: var(--muted); max-width: 620px; }
        .hero-img { width: 100%; height: 480px; object-fit: cover; border-radius: 22px; border: 1px solid var(--line); box-shadow: 0 20px 60px rgba(0,0,0,.5); }
        .hero-panel { width: 100%; height: 480px; border-radius: 22px; border: 1px solid var(--line); display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(124,92,255,.22), rgba(0,212,255,.14)); color: #fff; }
        .stat .num { font-size: 1.7rem; font-weight: 800; color: #fff; }
        .stat .lbl { font-size: .72rem; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); }

        .feature-card { background: var(--panel); border: 1px solid var(--line); border-radius: 18px; padding: 28px; height: 100%; transition: transform .25s, border-color .25s; }
        .feature-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .feature-icon { width: 58px; height: 58px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; background: linear-gradient(135deg, var(--primary), var(--secondary)); margin-bottom: 16px; }
        .feature-card h5 { color: #fff; }

        .about-img { width: 100%; border-radius: 22px; border: 1px solid var(--line); box-shadow: 0 20px 50px rgba(0,0,0,.45); object-fit: cover; }

        .filter-btn { border: 1px solid var(--line); background: transparent; color: var(--muted); border-radius: 30px; padding: 9px 18px; font-size: .85rem; margin: 0 4px 8px 0; }
        .filter-btn.active, .filter-btn:hover { background: linear-gradient(135deg, var(--primary), var(--secondary)); border-color: transparent; color: #fff; }

        .product-card { background: var(--panel); border: 1px solid var(--line); border-radius: 18px; overflow: hidden; height: 100%; transition: transform .2s, border-color .2s; }
        .product-card:hover { transform: translateY(-4px); border-color: var(--primary); }
        .product-img { width: 100%; height: 210px; object-fit: cover; background: #0f1219; }
        .product-img-placeholder { width: 100%; height: 210px; display: flex; align-items: center; justify-content: center; background: #0f1219; color: #3a4252; font-size: 3rem; }
        .product-cat { font-size: .7rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--secondary); }
        .product-price { color: #fff; font-weight: 800; }

        .gallery-item { border-radius: 16px; overflow: hidden; cursor: pointer; position: relative; border: 1px solid var(--line); }
        .gallery-item img { width: 100%; height: 220px; object-fit: cover; transition: transform .45s; }
        .gallery-item:hover img { transform: scale(1.06); }
        .gallery-item::after { content: '\f00e'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; background: rgba(0,0,0,.5); opacity: 0; transition: opacity .25s; }
        .gallery-item:hover::after { opacity: 1; }

        .contact-card { background: var(--panel); border: 1px solid var(--line); border-radius: 20px; padding: 28px; height: 100%; }
        .contact-card i { color: var(--secondary); width: 24px; }
        .contact-card a { color: var(--text); text-decoration: none; }
        .contact-card a:hover { color: var(--secondary); }
        .map-wrap { border: 1px solid var(--line); border-radius: 20px; min-height: 330px; overflow: hidden; }
        .map-wrap iframe { width: 100%; height: 100%; min-height: 330px; border: 0; filter: grayscale(20%); }

        footer { background: #06070a; color: var(--muted); border-top: 1px solid var(--line); }
        footer h6 { color: #fff; }
        footer a { color: var(--muted); text-decoration: none; }
        footer a:hover { color: #fff; }
        .social-link { width: 38px; height: 38px; border: 1px solid var(--line); display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; }

        .wa-float { position: fixed; right: 20px; bottom: 22px; z-index: 1040; width: 56px; height: 56px; border-radius: 50%; background: #25d366; color: #fff; font-size: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(0,0,0,.4); }
        .wa-float:hover { color: #fff; }
        .to-top { position: fixed; right: 24px; bottom: 90px; z-index: 1039; width: 42px; height: 42px; border-radius: 50%; border: 1px solid var(--line); background: var(--panel); color: #fff; display: none; }
        .to-top.show { display: block; }

        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        @media (max-width: 991px) { .hero-img, .hero-panel { height: 360px; } }
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
                    <a href="{{ route('dashboard') }}" class="btn btn-dark-theme"><i class="fas fa-gauge-high me-1"></i> Ir al sistema</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-dark-theme"><i class="fas fa-right-to-bracket me-1"></i> Iniciar sesión</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<section class="hero" id="inicio">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                @if($web['hero_subtitle'])<div class="eyebrow mb-3">{{ $web['hero_subtitle'] }}</div>@endif
                <h1 class="mb-3">{{ $web['hero_title'] }}</h1>
                @if($web['hero_description'])<p class="lead mb-4">{{ $web['hero_description'] }}</p>@endif
                <div class="d-flex flex-wrap gap-2">
                    @if($web['show_catalog'] && $products->count())<a href="#catalogo" class="btn btn-dark-theme">Ver catálogo</a>@endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-outline-dark-theme"><i class="fab fa-whatsapp me-1"></i> WhatsApp</a>@endif
                </div>
                @if($products->count() || $categories->count())
                <div class="row g-4 mt-5">
                    <div class="col-4"><div class="stat"><div class="num">{{ $products->count() }}</div><div class="lbl">Productos</div></div></div>
                    <div class="col-4"><div class="stat"><div class="num">{{ $categories->count() }}</div><div class="lbl">Categorías</div></div></div>
                    <div class="col-4"><div class="stat"><div class="num"><i class="fas fa-star"></i></div><div class="lbl">Calidad</div></div></div>
                </div>
                @endif
            </div>
            <div class="col-lg-6 reveal">
                @if($web['hero_image'])
                    <img src="{{ asset('storage/' . $web['hero_image']) }}" class="hero-img" alt="{{ $web['hero_title'] }}">
                @else
                    <div class="hero-panel"><i class="fas fa-utensils" style="font-size:5rem; opacity:.85;"></i></div>
                @endif
            </div>
        </div>
    </div>
</section>

@include('website.partials.slider')

@if($web['feature_1_title'] || $web['feature_2_title'] || $web['feature_3_title'])
<section class="py-5" id="beneficios">
    <div class="container py-4">
        <div class="text-center mb-5 reveal"><div class="eyebrow mb-2">Beneficios</div><h2 class="section-title">{{ $web['features_title'] }}</h2></div>
        <div class="row g-4">
            @foreach([[1, 'fa-award'], [2, 'fa-hand-holding-heart'], [3, 'fa-motorcycle']] as [$i, $icon])
            @if($web['feature_'.$i.'_title'] || $web['feature_'.$i.'_text'])
            <div class="col-md-4 reveal">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas {{ $icon }}"></i></div>
                    <h5 class="fw-bold mb-2">{{ $web['feature_'.$i.'_title'] }}</h5>
                    <p class="muted mb-0">{{ $web['feature_'.$i.'_text'] }}</p>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif

@if($web['about_text'] || $web['about_image'])
<section class="py-5" id="nosotros">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            @if($web['about_image'])
            <div class="col-lg-5 reveal"><img src="{{ asset('storage/' . $web['about_image']) }}" class="about-img" alt="{{ $web['about_title'] }}"></div>
            @endif
            <div class="{{ $web['about_image'] ? 'col-lg-7' : 'col-lg-9 mx-auto' }} reveal">
                <div class="eyebrow mb-2">Conócenos</div>
                <h2 class="section-title mb-3">{{ $web['about_title'] }}</h2>
                <div style="white-space: pre-line; color:var(--muted); line-height:1.9;">{{ $web['about_text'] }}</div>
            </div>
        </div>
    </div>
</section>
@endif

@if($web['show_catalog'] && $products->count())
<section class="py-5" id="catalogo">
    <div class="container py-4">
        <div class="text-center mb-4 reveal"><div class="eyebrow mb-2">Nuestro menú</div><h2 class="section-title">{{ $web['catalog_title'] }}</h2></div>
        @if($categories->count())
        <div class="d-flex flex-wrap justify-content-center mb-3 reveal">
            <button type="button" class="filter-btn active" data-filter="all">Todos</button>
            @foreach($categories as $cat)<button type="button" class="filter-btn" data-filter="{{ $cat->id }}">{{ $cat->nombre }}</button>@endforeach
        </div>
        @endif
        <div class="row justify-content-center mb-4 reveal">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="fas fa-search"></i></span>
                    <input type="text" id="catalogSearch" class="form-control bg-transparent text-white border-start-0" placeholder="Buscar producto...">
                </div>
            </div>
        </div>
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
                        <h6 class="mb-1 fw-semibold text-white">{{ $product->descripcion }}</h6>
                        @if($web['show_prices'])<div class="product-price mt-1">S/ {{ number_format($product->precio, 2) }}</div>@endif
                        @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, quiero pedir: ' . $product->descripcion) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark-theme mt-3"><i class="fab fa-whatsapp me-1"></i> Pedir</a>@endif
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
<section class="py-5" id="galeria">
    <div class="container py-4">
        <div class="text-center mb-4 reveal"><div class="eyebrow mb-2">Nuestro trabajo</div><h2 class="section-title">{{ $web['gallery_title'] }}</h2></div>
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
        <div class="text-center mb-5 reveal"><div class="eyebrow mb-2">Estamos para ti</div><h2 class="section-title">Contáctanos</h2></div>
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5 reveal">
                <div class="contact-card">
                    <div class="row g-3">
                        @if($web['address'])<div class="col-12 d-flex"><i class="fas fa-location-dot"></i><span>{{ $web['address'] }}</span></div>@endif
                        @if($web['phone'])<div class="col-12 d-flex"><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $web['phone']) }}">{{ $web['phone'] }}</a></div>@endif
                        @if($web['email'])<div class="col-12 d-flex"><i class="fas fa-envelope"></i><a href="mailto:{{ $web['email'] }}">{{ $web['email'] }}</a></div>@endif
                        @if($waNumber)<div class="col-12 d-flex"><i class="fab fa-whatsapp"></i><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener">WhatsApp</a></div>@endif
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-facebook-f"></i></a>@endif
                        @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-instagram"></i></a>@endif
                        @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-whatsapp"></i></a>@endif
                    </div>
                </div>
            </div>
            <div class="col-lg-7 reveal">
                <div class="map-wrap h-100">
                    @if($web['address'])
                        <iframe src="https://www.google.com/maps?q={{ urlencode($web['address']) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación"></iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="background:var(--panel); color:var(--muted);"><div class="text-center"><i class="fas fa-map-location-dot fa-3x mb-2 d-block"></i><span>Configura tu dirección</span></div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<footer class="pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white mb-3">{{ $brandName }}</h5>
                <p class="small mb-0">{{ $web['footer_text'] }}</p>
            </div>
            <div class="col-lg-4">
                <h6 class="mb-3">Enlaces</h6>
                <div class="d-flex flex-column gap-1 small">
                    <a href="#inicio">Inicio</a>
                    @if($web['about_text'] || $web['about_image'])<a href="#nosotros">Nosotros</a>@endif
                    @if($web['show_catalog'] && $products->count())<a href="#catalogo">Catálogo</a>@endif
                    <a href="#contacto">Contacto</a>
                </div>
            </div>
            <div class="col-lg-4">
                <h6 class="mb-3">Contacto</h6>
                <div class="d-flex flex-column gap-1 small">
                    @if($web['address'])<span><i class="fas fa-location-dot me-2"></i>{{ $web['address'] }}</span>@endif
                    @if($web['phone'])<span><i class="fas fa-phone me-2"></i>{{ $web['phone'] }}</span>@endif
                    @if($web['email'])<span><i class="fas fa-envelope me-2"></i>{{ $web['email'] }}</span>@endif
                </div>
            </div>
        </div>
        <hr class="border-secondary opacity-25 my-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small">
            <span>&copy; {{ date('Y') }} {{ $brandName }}. Todos los derechos reservados.</span>
            <a href="{{ route('login') }}"><i class="fas fa-lock me-1"></i> Acceso al sistema</a>
        </div>
    </div>
</footer>

@if($gallery->count())
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <img id="galleryModalImg" src="" alt="Galería" class="w-100 rounded-3">
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
