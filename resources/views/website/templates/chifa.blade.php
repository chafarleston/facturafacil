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
        :root { --primary: {{ $web['primary_color'] ?: '#9e1b1b' }}; --secondary: {{ $web['secondary_color'] ?: '#d4af37' }}; --cream: #fff8ee; --ink: #3d0f0a; --line: #e6c9a0; --gold: #d4af37; }
        * { font-family: '{{ $font }}', sans-serif; }
        html { scroll-behavior: smooth; }
        body {
            background:
                radial-gradient(circle at 12% 8%, rgba(212,175,55,.12) 0 2px, transparent 3px),
                radial-gradient(circle at 88% 18%, rgba(158,27,27,.08) 0 2px, transparent 3px),
                var(--cream);
            color: #5a3328;
            overflow-x: hidden;
        }
        section { scroll-margin-top: 86px; }

        .navbar { background: #7a0c0c; border-bottom: 2px solid var(--secondary); box-shadow: 0 4px 18px rgba(61,15,10,.16); }
        .navbar-brand img { height: 44px; width: auto; margin-right: 10px; }
        .navbar-brand span { font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #fff6dc; }
        .navbar .nav-link { color: #ffe8bf; font-size: .85rem; letter-spacing: 1.5px; text-transform: uppercase; }
        .navbar .nav-link:hover { color: var(--gold); }

        .btn-classic { background: var(--primary); color: #fff; border: 1px solid var(--primary); border-radius: 4px; padding: 12px 26px; font-size: .8rem; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600; }
        .btn-classic:hover { background: var(--gold); border-color: var(--gold); color: var(--ink); }
        .btn-outline-classic { border: 1px solid var(--gold); color: #fff6dc; border-radius: 4px; padding: 11px 24px; font-size: .8rem; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600; }
        .btn-outline-classic:hover { background: var(--gold); border-color: var(--gold); color: var(--ink); }

        .eyebrow { font-size: .72rem; letter-spacing: 4px; text-transform: uppercase; color: var(--primary); font-weight: 700; }
        .section-title { font-weight: 700; color: var(--ink); font-size: clamp(1.7rem, 3.2vw, 2.5rem); }
        .rule { width: 70px; height: 2px; background: var(--gold); }

        .hero {
            padding: 80px 0 60px;
            text-align: center;
            border-bottom: 1px solid var(--line);
            background:
                radial-gradient(circle at 15% 20%, rgba(212,175,55,.16) 0 2px, transparent 3px),
                radial-gradient(circle at 85% 80%, rgba(158,27,27,.08) 0 2px, transparent 3px),
                linear-gradient(180deg, #fffdf8, #fff1dc);
            position: relative;
        }
        .hero::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 18px;
            width: 54px;
            height: 54px;
            transform: translateX(-50%);
            border: 2px solid var(--gold);
            border-radius: 50%;
            box-shadow: 0 0 0 6px rgba(212,175,55,.12);
        }
        .hero h1 { font-weight: 700; color: var(--ink); font-size: clamp(2.2rem, 5vw, 3.8rem); line-height: 1.1; }
        .hero .lead { color: #7a4d3d; max-width: 640px; margin: 0 auto; }
        .hero-img { width: 100%; max-width: 460px; border: 8px solid #fff; box-shadow: 0 14px 34px rgba(61,15,10,.22); }

        .feature { border: 1px solid var(--line); background: #fffdf8; padding: 26px; height: 100%; border-top: 3px solid var(--gold); border-radius: 4px; }
        .feature i { font-size: 1.6rem; color: var(--primary); }
        .feature h5 { color: var(--ink); font-weight: 700; margin: 12px 0 8px; }

        .about-img { width: 100%; border: 8px solid #fff; box-shadow: 0 14px 34px rgba(61,15,10,.18); object-fit: cover; }

        .filter-btn { border: 1px solid var(--line); background: #fffdf8; color: #7a4d3d; border-radius: 4px; padding: 9px 18px; font-size: .78rem; letter-spacing: 1px; text-transform: uppercase; margin: 0 4px 8px 0; }
        .filter-btn.active, .filter-btn:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

        .product-card { border: 1px solid var(--line); background: #fffdf8; border-radius: 4px; overflow: hidden; height: 100%; transition: border-color .2s, box-shadow .2s; }
        .product-card:hover { border-color: var(--gold); box-shadow: 0 10px 26px rgba(61,15,10,.12); }
        .product-img { width: 100%; height: 220px; object-fit: cover; background: #f1ece3; }
        .product-img-placeholder { width: 100%; height: 220px; display: flex; align-items: center; justify-content: center; background: #fff1dc; color: #cfb27c; font-size: 3rem; }
        .product-cat { font-size: .68rem; letter-spacing: 2px; text-transform: uppercase; color: var(--primary); }
        .product-price { color: var(--primary); font-weight: 700; font-size: 1.1rem; }

        .gallery-item { overflow: hidden; cursor: pointer; position: relative; border: 1px solid var(--line); }
        .gallery-item img { width: 100%; height: 220px; object-fit: cover; transition: transform .5s; }
        .gallery-item:hover img { transform: scale(1.06); }
        .gallery-item::after { content: '\f00e'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; background: rgba(0,0,0,.4); opacity: 0; transition: opacity .25s; }
        .gallery-item:hover::after { opacity: 1; }

        .contact-box { border: 1px solid var(--line); background: #fffdf8; padding: 28px; height: 100%; border-radius: 4px; }
        .contact-box i { color: var(--primary); width: 24px; }
        .contact-box a { color: #5a3328; text-decoration: none; }
        .contact-box a:hover { color: var(--primary); }
        .map-wrap { border: 1px solid var(--line); min-height: 320px; border-radius: 4px; overflow: hidden; }
        .map-wrap iframe { width: 100%; height: 100%; min-height: 320px; border: 0; }

        footer { background: #3d0f0a; color: #f3d9a4; border-top: 3px solid var(--gold); }
        footer h6 { color: #fff6dc; letter-spacing: 2px; text-transform: uppercase; font-size: .78rem; }
        footer a { color: #f3d9a4; text-decoration: none; }
        footer a:hover { color: #fff; }
        .social-link { width: 36px; height: 36px; border: 1px solid rgba(212,175,55,.55); display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; }

        .wa-float { position: fixed; right: 20px; bottom: 22px; z-index: 1040; width: 54px; height: 54px; border-radius: 50%; background: #25d366; color: #fff; font-size: 27px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(0,0,0,.25); }
        .wa-float:hover { color: #fff; }
        .to-top { position: fixed; right: 24px; bottom: 88px; z-index: 1039; width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--gold); background: #fffdf8; color: var(--primary); display: none; }
        .to-top.show { display: block; }

        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        @media (max-width: 991px) { .hero-img { margin-top: 24px; } }
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
                    <a href="{{ route('dashboard') }}" class="btn btn-classic"><i class="fas fa-gauge-high me-1"></i> Ir al sistema</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-classic"><i class="fas fa-right-to-bracket me-1"></i> Iniciar sesión</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<section class="hero" id="inicio">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal text-lg-start">
                @if($web['hero_subtitle'])<div class="eyebrow mb-3">{{ $web['hero_subtitle'] }}</div>@endif
                <h1 class="mb-3">{{ $web['hero_title'] }}</h1>
                <div class="rule mx-auto mx-lg-0 my-4"></div>
                @if($web['hero_description'])<p class="lead mb-4">{{ $web['hero_description'] }}</p>@endif
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
                    @if($web['show_catalog'] && $products->count())<a href="#catalogo" class="btn btn-classic">Ver catálogo</a>@endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-outline-classic"><i class="fab fa-whatsapp me-1"></i> WhatsApp</a>@endif
                </div>
            </div>
            <div class="col-lg-6 reveal">
                @if($web['hero_image'])
                    <img src="{{ asset('storage/' . $web['hero_image']) }}" class="hero-img" alt="{{ $web['hero_title'] }}">
                @else
                    <div class="hero-img d-flex align-items-center justify-content-center" style="height:380px; background:linear-gradient(135deg, var(--primary), var(--secondary)); color:#fff;">
                        <i class="fas fa-utensils" style="font-size:5rem; opacity:.85;"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@include('website.partials.slider')

@if($web['feature_1_title'] || $web['feature_2_title'] || $web['feature_3_title'])
<section class="py-5" id="beneficios" style="background:#fff;">
    <div class="container py-4">
        <div class="text-center mb-5 reveal"><div class="eyebrow mb-2">Beneficios</div><h2 class="section-title">{{ $web['features_title'] }}</h2></div>
        <div class="row g-4">
            @foreach([[1, 'fa-award'], [2, 'fa-hand-holding-heart'], [3, 'fa-motorcycle']] as [$i, $icon])
            @if($web['feature_'.$i.'_title'] || $web['feature_'.$i.'_text'])
            <div class="col-md-4 reveal">
                <div class="feature">
                    <i class="fas {{ $icon }}"></i>
                    <h5>{{ $web['feature_'.$i.'_title'] }}</h5>
                    <p class="mb-0 small" style="color:#6b655b;">{{ $web['feature_'.$i.'_text'] }}</p>
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
                <div class="rule mb-4"></div>
                <div style="white-space: pre-line; color:#5f5a52; line-height:1.9;">{{ $web['about_text'] }}</div>
            </div>
        </div>
    </div>
</section>
@endif

@include('website.partials.virtual-menu')

@if($web['show_catalog'] && $products->count())
<section class="py-5" id="catalogo" style="background:#fff;">
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
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="catalogSearch" class="form-control border-start-0" placeholder="Buscar producto...">
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
                        <h6 class="mb-1 fw-semibold">{{ $product->descripcion }}</h6>
                        @if($web['show_prices'])<div class="product-price mt-1">S/ {{ number_format($product->precio, 2) }}</div>@endif
                        @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, quiero pedir: ' . $product->descripcion) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-classic mt-3"><i class="fab fa-whatsapp me-1"></i> Pedir</a>@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div id="catalogEmpty" class="text-center text-muted py-4" style="display:none;"><i class="fas fa-search fa-2x mb-2 d-block"></i> No se encontraron productos.</div>
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
<section class="py-5" id="contacto" style="background:#fff;">
    <div class="container py-4">
        <div class="text-center mb-5 reveal"><div class="eyebrow mb-2">Estamos para ti</div><h2 class="section-title">Contáctanos</h2></div>
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5 reveal">
                <div class="contact-box">
                    <div class="row g-3">
                        @if($web['address'])<div class="col-12 d-flex"><i class="fas fa-location-dot"></i><span>{{ $web['address'] }}</span></div>@endif
                        @if($web['phone'])<div class="col-12 d-flex"><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $web['phone']) }}">{{ $web['phone'] }}</a></div>@endif
                        @if($web['email'])<div class="col-12 d-flex"><i class="fas fa-envelope"></i><a href="mailto:{{ $web['email'] }}">{{ $web['email'] }}</a></div>@endif
                        @if($waNumber)<div class="col-12 d-flex"><i class="fab fa-whatsapp"></i><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener">WhatsApp</a></div>@endif
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-classic"><i class="fab fa-facebook-f me-1"></i> Facebook</a>@endif
                        @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-classic"><i class="fab fa-instagram me-1"></i> Instagram</a>@endif
                    </div>
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-classic w-100 mt-4"><i class="fab fa-whatsapp me-1"></i> Pedir por WhatsApp</a>@endif
                </div>
            </div>
            <div class="col-lg-7 reveal">
                <div class="map-wrap h-100">
                    @if($web['address'])
                        <iframe src="https://www.google.com/maps?q={{ urlencode($web['address']) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación"></iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="background:#f1ece3; color:#b3a996;"><div class="text-center"><i class="fas fa-map-location-dot fa-3x mb-2 d-block"></i><span>Configura tu dirección</span></div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<footer class="py-4">
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
