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
        :root { --primary: {{ $web['primary_color'] ?: '#b08d57' }}; --secondary: {{ $web['secondary_color'] ?: '#232323' }}; --cream: #f8f5ef; --ink: #202020; }
        * { font-family: '{{ $font }}', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background: var(--cream); color: #4a4a4a; overflow-x: hidden; }
        section { scroll-margin-top: 84px; }

        .navbar { background: var(--cream); border-bottom: 1px solid rgba(0,0,0,.08); }
        .navbar-brand img { height: 46px; width: auto; margin-right: 12px; }
        .navbar-brand span { letter-spacing: 2px; text-transform: uppercase; font-weight: 700; color: var(--ink); }
        .navbar .nav-link { color: #5a5a5a; font-size: .86rem; letter-spacing: 2px; text-transform: uppercase; font-weight: 500; }
        .navbar .nav-link:hover { color: var(--primary); }

        .btn-elegant { background: var(--ink); border: 1px solid var(--ink); color: #fff; border-radius: 0; letter-spacing: 1.5px; text-transform: uppercase; font-size: .78rem; padding: 12px 24px; font-weight: 600; }
        .btn-elegant:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-outline-elegant { border: 1px solid var(--ink); color: var(--ink); border-radius: 0; letter-spacing: 1.5px; text-transform: uppercase; font-size: .78rem; padding: 11px 22px; font-weight: 600; }
        .btn-outline-elegant:hover { background: var(--ink); color: #fff; }

        .eyebrow { letter-spacing: 4px; text-transform: uppercase; font-size: .72rem; color: var(--primary); font-weight: 700; }
        .section-title { font-weight: 700; color: var(--ink); font-size: clamp(1.7rem, 3.2vw, 2.6rem); }
        .divider { width: 74px; height: 2px; background: var(--primary); }

        .hero { padding: 80px 0 70px; }
        .hero h1 { font-weight: 700; color: var(--ink); font-size: clamp(2.2rem, 4.8vw, 4rem); line-height: 1.08; }
        .hero .lead { color: #5f5f5f; font-size: 1.08rem; max-width: 560px; }
        .hero-img { width: 100%; height: 520px; object-fit: cover; border-radius: 2px; box-shadow: 20px 20px 0 rgba(176,141,87,.22); }
        .hero-panel { width: 100%; height: 520px; border-radius: 2px; background: linear-gradient(145deg, var(--secondary), var(--primary)); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 20px 20px 0 rgba(176,141,87,.22); }
        .hero-panel i { font-size: 5rem; opacity: .85; }

        .feature { border-top: 2px solid var(--ink); padding-top: 22px; height: 100%; }
        .feature .num { font-size: 1.1rem; color: var(--primary); font-weight: 700; letter-spacing: 2px; }
        .feature h5 { color: var(--ink); font-weight: 700; margin: 10px 0; }

        .about-img { width: 100%; border-radius: 2px; box-shadow: -20px 20px 0 rgba(176,141,87,.22); object-fit: cover; }
        .about-badge { background: var(--ink); color: #fff; padding: 14px 22px; position: absolute; bottom: -20px; right: -12px; letter-spacing: 1px; }

        .filter-btn { border: 1px solid #d8d1c4; background: transparent; color: #5a5a5a; border-radius: 0; padding: 9px 18px; font-size: .78rem; letter-spacing: 1.5px; text-transform: uppercase; margin: 0 4px 8px 0; transition: all .2s; }
        .filter-btn.active, .filter-btn:hover { background: var(--ink); border-color: var(--ink); color: #fff; }

        .product-card { border: 1px solid #e4ded2; background: #fff; border-radius: 2px; overflow: hidden; height: 100%; transition: border-color .2s, transform .2s; }
        .product-card:hover { border-color: var(--primary); transform: translateY(-3px); }
        .product-img { width: 100%; height: 230px; object-fit: cover; background: #f1ede5; }
        .product-img-placeholder { width: 100%; height: 230px; display: flex; align-items: center; justify-content: center; background: #f1ede5; color: #cfc7b8; font-size: 3rem; }
        .product-cat { font-size: .68rem; letter-spacing: 2px; text-transform: uppercase; color: var(--primary); }
        .product-price { color: var(--ink); font-weight: 700; font-size: 1.1rem; }

        .gallery-item { overflow: hidden; cursor: pointer; position: relative; border-radius: 2px; }
        .gallery-item img { width: 100%; height: 230px; object-fit: cover; transition: transform .5s; }
        .gallery-item:hover img { transform: scale(1.08); }
        .gallery-item::after { content: '\f00e'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; background: rgba(0,0,0,.4); opacity: 0; transition: opacity .25s; }
        .gallery-item:hover::after { opacity: 1; }

        .contact-box { border: 1px solid #e4ded2; background: #fff; padding: 28px; height: 100%; }
        .contact-box i { color: var(--primary); width: 26px; }
        .contact-box a { color: #4a4a4a; text-decoration: none; }
        .contact-box a:hover { color: var(--primary); }
        .map-wrap { min-height: 340px; border: 1px solid #e4ded2; }
        .map-wrap iframe { width: 100%; height: 100%; min-height: 340px; border: 0; }

        footer { background: var(--secondary); color: #c9c2b5; }
        footer h6 { color: #fff; letter-spacing: 2px; text-transform: uppercase; font-size: .8rem; }
        footer a { color: #c9c2b5; text-decoration: none; }
        footer a:hover { color: #fff; }
        .social-link { width: 38px; height: 38px; border: 1px solid rgba(255,255,255,.3); display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; }

        .wa-float { position: fixed; right: 20px; bottom: 22px; z-index: 1040; width: 56px; height: 56px; border-radius: 50%; background: #25d366; color: #fff; font-size: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(0,0,0,.28); }
        .wa-float:hover { color: #fff; }
        .to-top { position: fixed; right: 24px; bottom: 90px; z-index: 1039; width: 42px; height: 42px; border-radius: 50%; border: none; background: var(--ink); color: #fff; display: none; }
        .to-top.show { display: block; }

        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        @media (max-width: 991px) {
            .hero-img, .hero-panel { height: 380px; box-shadow: none; }
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
                    <a href="{{ route('dashboard') }}" class="btn btn-elegant"><i class="fas fa-gauge-high me-1"></i> Ir al sistema</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-elegant"><i class="fas fa-right-to-bracket me-1"></i> Iniciar sesión</a>
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
                <h1 class="mb-3">{{ $web['hero_title'] }}</h1>
                <div class="divider my-4"></div>
                @if($web['hero_description'])<p class="lead mb-4">{{ $web['hero_description'] }}</p>@endif
                <div class="d-flex flex-wrap gap-2">
                    @if($web['show_catalog'] && $products->count())<a href="#catalogo" class="btn btn-elegant">Ver catálogo</a>@endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-outline-elegant"><i class="fab fa-whatsapp me-1"></i> Pedir por WhatsApp</a>@endif
                </div>
                @if($products->count() || $categories->count())
                <div class="row g-4 mt-4">
                    <div class="col-4"><div class="fs-3 fw-bold" style="color:var(--ink);">{{ $products->count() }}</div><div class="eyebrow">Productos</div></div>
                    <div class="col-4"><div class="fs-3 fw-bold" style="color:var(--ink);">{{ $categories->count() }}</div><div class="eyebrow">Categorías</div></div>
                    <div class="col-4"><div class="fs-3 fw-bold" style="color:var(--ink);"><i class="fas fa-star"></i></div><div class="eyebrow">Calidad</div></div>
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
<section class="py-5" id="beneficios" style="background:#fff;">
    <div class="container py-4">
        <div class="text-center mb-5 reveal"><div class="eyebrow mb-2">Beneficios</div><h2 class="section-title">{{ $web['features_title'] }}</h2></div>
        <div class="row g-4">
            @foreach([[1, 'fa-award'], [2, 'fa-hand-holding-heart'], [3, 'fa-motorcycle']] as [$i, $icon])
            @if($web['feature_'.$i.'_title'] || $web['feature_'.$i.'_text'])
            <div class="col-md-4 reveal">
                <div class="feature">
                    <div class="num">0{{ $i }}</div>
                    <h5><i class="fas {{ $icon }} me-2" style="color:var(--primary);"></i>{{ $web['feature_'.$i.'_title'] }}</h5>
                    <p class="text-muted mb-0">{{ $web['feature_'.$i.'_text'] }}</p>
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
            <div class="col-lg-5 reveal">
                <div class="position-relative">
                    <img src="{{ asset('storage/' . $web['about_image']) }}" class="about-img" alt="{{ $web['about_title'] }}">
                    @if($products->count())<div class="about-badge">+{{ $products->count() }} productos</div>@endif
                </div>
            </div>
            @endif
            <div class="{{ $web['about_image'] ? 'col-lg-7' : 'col-lg-9 mx-auto' }} reveal">
                <div class="eyebrow mb-2">Conócenos</div>
                <h2 class="section-title mb-3">{{ $web['about_title'] }}</h2>
                <div class="divider mb-4"></div>
                <div style="white-space: pre-line; color:#5f5f5f; line-height:1.9; font-size:1.05rem;">{{ $web['about_text'] }}</div>
            </div>
        </div>
    </div>
</section>
@endif

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
                        @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, quiero pedir: ' . $product->descripcion) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-elegant mt-3"><i class="fab fa-whatsapp me-1"></i> Pedir</a>@endif
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
                        @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-elegant"><i class="fab fa-facebook-f me-1"></i> Facebook</a>@endif
                        @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-elegant"><i class="fab fa-instagram me-1"></i> Instagram</a>@endif
                    </div>
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-elegant w-100 mt-4"><i class="fab fa-whatsapp me-1"></i> Pedir por WhatsApp</a>@endif
                </div>
            </div>
            <div class="col-lg-7 reveal">
                <div class="map-wrap h-100">
                    @if($web['address'])
                        <iframe src="https://www.google.com/maps?q={{ urlencode($web['address']) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación"></iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="background:#f1ede5; color:#b3aa9a;"><div class="text-center"><i class="fas fa-map-location-dot fa-3x mb-2 d-block"></i><span>Configura tu dirección</span></div></div>
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
                <h5 class="text-white mb-3" style="letter-spacing:2px; text-transform:uppercase;">{{ $brandName }}</h5>
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
                <div class="d-flex gap-2 mt-3">
                    @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-facebook-f"></i></a>@endif
                    @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-instagram"></i></a>@endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="social-link"><i class="fab fa-whatsapp"></i></a>@endif
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
