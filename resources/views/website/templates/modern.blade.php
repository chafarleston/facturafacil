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
        :root { --primary: {{ $web['primary_color'] ?: '#e94560' }}; --secondary: {{ $web['secondary_color'] ?: '#3a1c4a' }}; --dark: #1d1e2c; }
        * { font-family: '{{ $font }}', sans-serif; }
        html { scroll-behavior: smooth; }
        body { color: #414141; overflow-x: hidden; }
        section { scroll-margin-top: 80px; }

        .navbar { background: rgba(255,255,255,.96); backdrop-filter: blur(8px); box-shadow: 0 2px 16px rgba(0,0,0,.06); }
        .navbar-brand img { height: 44px; width: auto; margin-right: 10px; }
        .navbar .nav-link { color: #414141; font-weight: 500; }
        .navbar .nav-link:hover { color: var(--primary); }

        .btn-custom { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
        .btn-custom:hover { filter: brightness(.92); color: #fff; }
        .btn-outline-custom { border: 2px solid var(--primary); color: var(--primary); font-weight: 600; }
        .btn-outline-custom:hover { background: var(--primary); color: #fff; }

        .hero {
            position: relative; min-height: 88vh; display: flex; align-items: center;
            color: #fff; overflow: hidden; background-size: cover; background-position: center;
            @if($web['hero_image'])
            background-image: linear-gradient(rgba(10,10,20,.62), rgba(10,10,20,.62)), url('{{ asset('storage/' . $web['hero_image']) }}');
            @else
            background-image: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 65%, var(--dark) 100%);
            @endif
        }
        .hero::after {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.12), transparent 45%);
            pointer-events: none;
        }
        .hero .container { position: relative; z-index: 1; }
        .hero-badge {
            display: inline-block; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.35);
            padding: 7px 18px; border-radius: 30px; font-size: .85rem; letter-spacing: 1.5px;
            text-transform: uppercase; margin-bottom: 18px;
        }
        .hero h1 { font-weight: 800; font-size: clamp(2.2rem, 5.2vw, 4rem); line-height: 1.1; }
        .hero p.lead { font-size: clamp(1rem, 1.6vw, 1.2rem); opacity: .95; max-width: 640px; }
        .hero-stats { border-top: 1px solid rgba(255,255,255,.25); margin-top: 34px; padding-top: 22px; }
        .hero-stats .num { font-size: 1.8rem; font-weight: 800; line-height: 1; }
        .hero-stats .lbl { font-size: .82rem; text-transform: uppercase; letter-spacing: 1px; opacity: .85; }

        .section-title { font-weight: 800; color: var(--dark); font-size: clamp(1.6rem, 3vw, 2.3rem); }
        .section-subtitle { color: #8a8a94; max-width: 620px; margin: 0 auto; }
        .section-eyebrow { color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: .8rem; }

        .feature-card { border: none; border-radius: 20px; background: #fff; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,.06); transition: transform .25s, box-shadow .25s; }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 18px 42px rgba(0,0,0,.12); }
        .feature-icon { width: 64px; height: 64px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #fff; background: linear-gradient(135deg, var(--primary), var(--secondary)); margin-bottom: 18px; }

        .about-img { border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,.16); width: 100%; object-fit: cover; }
        .about-badge { position: absolute; bottom: -18px; right: -12px; background: var(--primary); color: #fff; border-radius: 18px; padding: 14px 22px; font-weight: 700; box-shadow: 0 12px 26px rgba(0,0,0,.22); }

        .filter-btn { border: 2px solid #e8e8ee; background: #fff; color: #555; border-radius: 30px; padding: 8px 18px; font-size: .92rem; margin: 0 4px 8px 0; transition: all .2s; font-weight: 500; }
        .filter-btn.active, .filter-btn:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

        .product-card { border: none; border-radius: 18px; overflow: hidden; background: #fff; height: 100%; box-shadow: 0 6px 22px rgba(0,0,0,.07); transition: transform .22s, box-shadow .22s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 16px 36px rgba(0,0,0,.13); }
        .product-img { width: 100%; height: 210px; object-fit: cover; background: #f4f4f4; }
        .product-img-placeholder { width: 100%; height: 210px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg,#f6f6fa,#eceef5); color: #c3c6d4; font-size: 3rem; }
        .product-price { color: var(--primary); font-weight: 800; font-size: 1.2rem; }
        .product-cat { font-size: .74rem; text-transform: uppercase; letter-spacing: 1px; color: #9a9aa6; }

        .gallery-item { border-radius: 16px; overflow: hidden; cursor: pointer; position: relative; }
        .gallery-item img { width: 100%; height: 220px; object-fit: cover; transition: transform .4s; }
        .gallery-item:hover img { transform: scale(1.07); }
        .gallery-item::after { content: '\f00e'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.6rem; background: rgba(0,0,0,.35); opacity: 0; transition: opacity .25s; }
        .gallery-item:hover::after { opacity: 1; }

        .contact-card { background: #fff; border-radius: 20px; padding: 26px; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,.06); }
        .contact-card i { color: var(--primary); font-size: 1.25rem; width: 28px; }
        .contact-card a { color: #414141; text-decoration: none; }
        .contact-card a:hover { color: var(--primary); }
        .map-wrap { border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.08); min-height: 320px; }
        .map-wrap iframe { width: 100%; height: 100%; min-height: 320px; border: 0; }

        footer { background: var(--dark); color: #c9ccd8; }
        footer h6 { color: #fff; font-weight: 700; }
        footer a { color: #c9ccd8; text-decoration: none; }
        footer a:hover { color: #fff; }
        .social-link { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.1); display: inline-flex; align-items: center; justify-content: center; }

        .wa-float { position: fixed; right: 20px; bottom: 22px; z-index: 1040; width: 58px; height: 58px; border-radius: 50%; background: #25d366; color: #fff; font-size: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 22px rgba(0,0,0,.28); transition: transform .2s; }
        .wa-float:hover { color: #fff; transform: scale(1.07); }
        .to-top { position: fixed; right: 24px; bottom: 92px; z-index: 1039; width: 44px; height: 44px; border-radius: 50%; border: none; background: var(--primary); color: #fff; display: none; }
        .to-top.show { display: block; }

        .reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: none; }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#mainNav" tabindex="0">

<nav class="navbar navbar-expand-lg sticky-top py-2" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center fw-bold" href="#inicio">
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
                    <a href="{{ route('dashboard') }}" class="btn btn-custom btn-sm px-3"><i class="fas fa-gauge-high me-1"></i> Ir al sistema</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-custom btn-sm px-3"><i class="fas fa-right-to-bracket me-1"></i> Iniciar sesión</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<section class="hero" id="inicio">
    <div class="container py-5">
        <div class="col-lg-9">
            @if($web['hero_subtitle'])<span class="hero-badge">{{ $web['hero_subtitle'] }}</span>@endif
            <h1 class="mb-3">{{ $web['hero_title'] }}</h1>
            @if($web['hero_description'])<p class="lead mb-4">{{ $web['hero_description'] }}</p>@endif
            <div class="d-flex flex-wrap gap-2">
                @if($web['show_catalog'] && $products->count())<a href="#catalogo" class="btn btn-light px-4 py-2 fw-semibold"><i class="fas fa-box-open me-1"></i> Ver catálogo</a>@endif
                @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-custom px-4 py-2"><i class="fab fa-whatsapp me-1"></i> Pedir por WhatsApp</a>@endif
            </div>
            @if($products->count() || $categories->count())
            <div class="hero-stats row g-3">
                <div class="col-4 col-md-3"><div class="num">{{ $products->count() }}</div><div class="lbl">Productos</div></div>
                <div class="col-4 col-md-3"><div class="num">{{ $categories->count() }}</div><div class="lbl">Categorías</div></div>
                <div class="col-4 col-md-3"><div class="num"><i class="fas fa-star"></i></div><div class="lbl">Calidad</div></div>
            </div>
            @endif
        </div>
    </div>
</section>

@include('website.partials.slider')

@if($web['feature_1_title'] || $web['feature_2_title'] || $web['feature_3_title'])
<section class="py-5" id="beneficios">
    <div class="container py-4">
        <div class="text-center mb-5 reveal"><div class="section-eyebrow mb-2">Beneficios</div><h2 class="section-title">{{ $web['features_title'] }}</h2></div>
        <div class="row g-4">
            @foreach([[1, 'fa-award'], [2, 'fa-hand-holding-heart'], [3, 'fa-motorcycle']] as [$i, $icon])
            @if($web['feature_'.$i.'_title'] || $web['feature_'.$i.'_text'])
            <div class="col-md-4 reveal">
                <div class="feature-card p-4">
                    <div class="feature-icon"><i class="fas {{ $icon }}"></i></div>
                    <h5 class="fw-bold mb-2">{{ $web['feature_'.$i.'_title'] }}</h5>
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
<section class="py-5" id="nosotros" style="background:#fafafc;">
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
                <div class="section-eyebrow mb-2">Conócenos</div>
                <h2 class="section-title mb-3">{{ $web['about_title'] }}</h2>
                <div style="white-space: pre-line; color:#5a5a66; line-height:1.9; font-size:1.05rem;">{{ $web['about_text'] }}</div>
                @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="btn btn-custom mt-4 px-4"><i class="fab fa-whatsapp me-1"></i> Contáctanos</a>@endif
            </div>
        </div>
    </div>
</section>
@endif

@if($web['show_catalog'] && $products->count())
<section class="py-5" id="catalogo">
    <div class="container py-4">
        <div class="text-center mb-4 reveal"><div class="section-eyebrow mb-2">Nuestro menú</div><h2 class="section-title">{{ $web['catalog_title'] }}</h2></div>
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
        <div class="row g-4" id="catalogGrid">
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
                        @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, quiero pedir: ' . $product->descripcion) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success mt-3"><i class="fab fa-whatsapp me-1"></i> Pedir</a>@endif
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
<section class="py-5" id="galeria" style="background:#fafafc;">
    <div class="container py-4">
        <div class="text-center mb-4 reveal"><div class="section-eyebrow mb-2">Nuestro trabajo</div><h2 class="section-title">{{ $web['gallery_title'] }}</h2></div>
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
        <div class="text-center mb-5 reveal"><div class="section-eyebrow mb-2">Estamos para ti</div><h2 class="section-title">Contáctanos</h2><p class="section-subtitle">Escríbenos o visítanos. Con gusto te atendemos.</p></div>
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5 reveal">
                <div class="contact-card">
                    <div class="row g-3">
                        @if($web['address'])<div class="col-12 d-flex"><i class="fas fa-location-dot"></i><span>{{ $web['address'] }}</span></div>@endif
                        @if($web['phone'])<div class="col-12 d-flex"><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $web['phone']) }}">{{ $web['phone'] }}</a></div>@endif
                        @if($web['email'])<div class="col-12 d-flex"><i class="fas fa-envelope"></i><a href="mailto:{{ $web['email'] }}">{{ $web['email'] }}</a></div>@endif
                        @if($waNumber)<div class="col-12 d-flex"><i class="fab fa-whatsapp"></i><a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener">WhatsApp</a></div>@endif
                    </div>
                    @if($web['facebook'] || $web['instagram'])
                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        @if($web['facebook'])<a href="{{ $web['facebook'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fab fa-facebook me-1"></i> Facebook</a>@endif
                        @if($web['instagram'])<a href="{{ $web['instagram'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger"><i class="fab fa-instagram me-1"></i> Instagram</a>@endif
                    </div>
                    @endif
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hola, deseo hacer un pedido.') }}" target="_blank" rel="noopener" class="btn btn-custom w-100 mt-4"><i class="fab fa-whatsapp me-1"></i> Pedir por WhatsApp</a>@endif
                </div>
            </div>
            <div class="col-lg-7 reveal">
                <div class="map-wrap h-100">
                    @if($web['address'])
                        <iframe src="https://www.google.com/maps?q={{ urlencode($web['address']) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación"></iframe>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="background:linear-gradient(135deg,#f6f6fa,#eceef5); color:#a3a6b4;"><div class="text-center"><i class="fas fa-map-location-dot fa-3x mb-2 d-block"></i><span>Configura tu dirección para mostrar el mapa</span></div></div>
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
                <div class="d-flex align-items-center mb-3">
                    @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $brandName }}" style="height: {{ min((int) ($web['logo_height'] ?? 56), 56) }}px; width:auto; margin-right:10px;">@endif
                    <h5 class="text-white mb-0 fw-bold">{{ $brandName }}</h5>
                </div>
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
