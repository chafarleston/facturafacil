@php
    $font = $font ?? ($web['font'] ?: 'Poppins');
    $siteTitle = $web['hero_title'] ?: ($company->nombre_comercial ?: $company->razon_social);
    $description = trim((string) ($metaDescription ?? ($web['meta_description'] ?? '')));
    $description = $description ?: trim((string) ($web['hero_description'] ?? ''));
    $description = $description ?: trim((string) ($web['about_text'] ?? ''));

    if ($description === '') {
        $description = 'Menú, productos y pedidos en línea de ' . $siteTitle . '.';
    }

    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($description), 160);
    $isPoppins = strcasecmp($font, 'Poppins') === 0;
    $fontUrl = 'https://fonts.googleapis.com/css2?family=' . urlencode($font) . ':wght@400;500;600;700;800&display=optional';
@endphp

<title>{{ $siteTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta property="og:title" content="{{ $siteTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="website">
<meta name="theme-color" content="{{ $web['primary_color'] ?: '#e94560' }}">

@if(!empty($web['hero_image']))
    <link rel="preload" as="image" href="{{ asset('storage/' . $web['hero_image']) }}" fetchpriority="high">
@endif

@if($isPoppins)
    <link rel="preload" href="{{ asset('fonts/poppins/poppins-600.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/poppins/poppins-700.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/poppins/poppins-800.woff2') }}" as="font" type="font/woff2" crossorigin>
    <style>
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            font-display: optional;
            src: url('{{ asset('fonts/poppins/poppins-400.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 500;
            font-display: optional;
            src: url('{{ asset('fonts/poppins/poppins-500.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 600;
            font-display: optional;
            src: url('{{ asset('fonts/poppins/poppins-600.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700;
            font-display: optional;
            src: url('{{ asset('fonts/poppins/poppins-700.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 800;
            font-display: optional;
            src: url('{{ asset('fonts/poppins/poppins-800.woff2') }}') format('woff2');
        }
        @font-face {
            font-family: 'Poppins Fallback';
            src: local('Arial');
            size-adjust: 105%;
            ascent-override: 90%;
            descent-override: 22%;
            line-gap-override: 0%;
        }
    </style>
@else
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ $fontUrl }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ $fontUrl }}"></noscript>
@endif

<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
