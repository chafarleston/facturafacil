@extends('layouts.admin')
@section('title', 'Página Web')
@section('page_title', 'Página Web')

@section('content')
@php $webEnabled = \App\Models\Setting::isWebsiteEnabled(); @endphp

<div class="row">
    <div class="col-md-10 offset-md-1">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-globe"></i> Estado de la página web</h3>
                <div>
                    <span class="badge badge-{{ $webEnabled ? 'success' : 'secondary' }} mr-2">
                        {{ $webEnabled ? 'Habilitada' : 'Deshabilitada' }}
                    </span>
                    <form action="{{ route('website.toggle') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-{{ $webEnabled ? 'danger' : 'success' }}">
                            <i class="fas fa-{{ $webEnabled ? 'toggle-on' : 'toggle-off' }}"></i>
                            {{ $webEnabled ? 'Deshabilitar' : 'Habilitar' }}
                        </button>
                    </form>
                    @if($webEnabled)
                    <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-primary ml-1">
                        <i class="fas fa-external-link-alt"></i> Ver web
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    Cuando está <b>habilitada</b>, la raíz del sistema muestra esta página web.
                    Cuando está <b>deshabilitada</b>, la raíz muestra el login normal.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('website.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-palette"></i> Diseño</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Plantilla</label>
                            <select name="template" class="form-control">
                                <option value="modern" {{ old('template', $web['template']) === 'modern' ? 'selected' : '' }}>Moderna</option>
                                <option value="elegant" {{ old('template', $web['template']) === 'elegant' ? 'selected' : '' }}>Elegante</option>
                                <option value="minimal" {{ old('template', $web['template']) === 'minimal' ? 'selected' : '' }}>Minimalista</option>
                            </select>
                            <small class="text-muted">Moderna: colorida y con tarjetas. Elegante: sobria con líneas finas. Minimalista: limpia y espaciosa.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Color principal</label>
                            <input type="color" name="primary_color" class="form-control"
                                   value="{{ old('primary_color', $web['primary_color'] ?: '#e94560') }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Color secundario</label>
                            <input type="color" name="secondary_color" class="form-control"
                                   value="{{ old('secondary_color', $web['secondary_color'] ?: '#3a1c4a') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Tipo de letra</label>
                            <select name="font" class="form-control">
                                @foreach(['Poppins', 'Montserrat', 'Raleway', 'Playfair Display', 'Lora', 'Nunito'] as $fontOption)
                                    <option value="{{ $fontOption }}" {{ old('font', $web['font']) === $fontOption ? 'selected' : '' }}>{{ $fontOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-image"></i> Portada (Hero)</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Título principal</label>
                        <input type="text" name="hero_title" class="form-control" maxlength="150"
                               value="{{ old('hero_title', $web['hero_title']) }}">
                    </div>
                    <div class="form-group">
                        <label>Subtítulo</label>
                        <input type="text" name="hero_subtitle" class="form-control" maxlength="200"
                               value="{{ old('hero_subtitle', $web['hero_subtitle']) }}">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="hero_description" class="form-control" rows="3" maxlength="2000">{{ old('hero_description', $web['hero_description']) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Imagen de fondo</label>
                        <input type="file" name="hero_image" class="form-control-file" accept="image/*">
                        @if($web['hero_image'])
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $web['hero_image']) }}" alt="Hero" style="max-height:140px; border-radius:8px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_hero_image" id="remove_hero_image" value="1">
                                    <label class="form-check-label" for="remove_hero_image">Eliminar imagen actual</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-circle-info"></i> Nosotros</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="about_title" class="form-control" maxlength="150"
                               value="{{ old('about_title', $web['about_title']) }}">
                    </div>
                    <div class="form-group">
                        <label>Texto</label>
                        <textarea name="about_text" class="form-control" rows="5" maxlength="5000">{{ old('about_text', $web['about_text']) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" name="about_image" class="form-control-file" accept="image/*">
                        @if($web['about_image'])
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $web['about_image']) }}" alt="Nosotros" style="max-height:140px; border-radius:8px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_about_image" id="remove_about_image" value="1">
                                    <label class="form-check-label" for="remove_about_image">Eliminar imagen actual</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-star"></i> Beneficios</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Título de la sección</label>
                        <input type="text" name="features_title" class="form-control" maxlength="150"
                               value="{{ old('features_title', $web['features_title']) }}">
                    </div>
                    @for($i = 1; $i <= 3; $i++)
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>Beneficio {{ $i }} — Título</label>
                            <input type="text" name="feature_{{ $i }}_title" class="form-control" maxlength="100"
                                   value="{{ old('feature_'.$i.'_title', $web['feature_'.$i.'_title']) }}">
                        </div>
                        <div class="col-md-7 form-group">
                            <label>Beneficio {{ $i }} — Texto</label>
                            <input type="text" name="feature_{{ $i }}_text" class="form-control" maxlength="300"
                                   value="{{ old('feature_'.$i.'_text', $web['feature_'.$i.'_text']) }}">
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-box-open"></i> Catálogo</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Título del catálogo</label>
                        <input type="text" name="catalog_title" class="form-control" maxlength="150"
                               value="{{ old('catalog_title', $web['catalog_title']) }}">
                    </div>
                    <div class="form-group">
                        <label>Título de la galería</label>
                        <input type="text" name="gallery_title" class="form-control" maxlength="150"
                               value="{{ old('gallery_title', $web['gallery_title']) }}">
                        <small class="text-muted">La galería muestra las imágenes de los productos.</small>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_catalog" id="show_catalog" value="1"
                            {{ old('show_catalog', $web['show_catalog']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_catalog">Mostrar catálogo de productos</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_prices" id="show_prices" value="1"
                            {{ old('show_prices', $web['show_prices']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_prices">Mostrar precios</label>
                    </div>
                    <small class="text-muted">Se muestran solo los productos ACTIVOS de la empresa principal.</small>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-address-book"></i> Contacto</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Teléfono</label>
                            <input type="text" name="phone" class="form-control" maxlength="50"
                                   value="{{ old('phone', $web['phone']) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" maxlength="150"
                                   value="{{ old('email', $web['email']) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="address" class="form-control" maxlength="250"
                               value="{{ old('address', $web['address']) }}">
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" maxlength="50"
                                   placeholder="+51 999 999 999" value="{{ old('whatsapp', $web['whatsapp']) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Facebook (URL)</label>
                            <input type="text" name="facebook" class="form-control" maxlength="250"
                                   value="{{ old('facebook', $web['facebook']) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Instagram (URL)</label>
                            <input type="text" name="instagram" class="form-control" maxlength="250"
                                   value="{{ old('instagram', $web['instagram']) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shoe-prints"></i> Pie de página</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Texto del pie de página</label>
                        <input type="text" name="footer_text" class="form-control" maxlength="500"
                               value="{{ old('footer_text', $web['footer_text']) }}">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar configuración
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
