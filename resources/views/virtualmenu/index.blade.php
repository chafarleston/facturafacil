@extends('layouts.admin')
@section('title', 'Menú Virtual')
@section('page_title', 'Menú Virtual')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Revisa los campos marcados.
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book-open"></i> Configuración del Menú Virtual</h3>
            </div>

            <form method="POST" action="{{ route('virtual-menu.update') }}">
                @csrf
                <div class="card-body">

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="enabled" id="enabled" value="1"
                            {{ old('enabled', $enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="enabled">
                            Activar Menú Virtual público
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="domain">Dominio público</label>
                        <input type="url" name="domain" id="domain" class="form-control"
                               maxlength="255" placeholder="https://facturafacil.restauranterealcomputer.pro"
                               value="{{ old('domain', $domain) }}" required>
                        <small class="text-muted">La página pública será el dominio indicado + <b>/menuvirtual</b>.</small>
                    </div>

                    <div class="form-group">
                        <label>Enlace público</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $publicUrl }}" readonly>
                            <div class="input-group-append">
                                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Abrir
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Se emparejan por orden: <b>Menú 1</b> con <b>Segundo 1</b>, <b>Menú 2</b> con <b>Segundo 2</b>, y así sucesivamente.
                        Si dejas un segundo vacío, ese menú se mostrará solo con el menú principal.
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <h5 class="mb-3"><i class="fas fa-utensils"></i> Platos de menú (5)</h5>
                            @for($i = 0; $i < 5; $i++)
                                <div class="form-group">
                                    <label for="menu_name_{{ $i }}">Menú {{ $i + 1 }}</label>
                                    <input type="text" name="menu_names[]" id="menu_name_{{ $i }}" class="form-control"
                                           maxlength="150" placeholder="Escribe el nombre del plato"
                                           value="{{ old('menu_names.' . $i, $menuNames[$i] ?? '') }}">
                                </div>
                            @endfor
                        </div>

                        <div class="col-md-5">
                            <h5 class="mb-3"><i class="fas fa-money-bill-wave"></i> Costo por menú</h5>
                            @for($i = 0; $i < 5; $i++)
                                <div class="form-group">
                                    <label for="menu_price_{{ $i }}">Costo Menú {{ $i + 1 }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" name="menu_prices[]" id="menu_price_{{ $i }}" class="form-control"
                                               min="0" max="999999.99" step="0.01" placeholder="0.00"
                                               value="{{ old('menu_prices.' . $i, $menuPrices[$i] ?? '') }}">
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-bowl-rice"></i> Segundos (5)</h5>
                    <div class="row">
                        @for($i = 0; $i < 5; $i++)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="second_name_{{ $i }}">Segundo {{ $i + 1 }}</label>
                                    <input type="text" name="second_names[]" id="second_name_{{ $i }}" class="form-control"
                                           maxlength="150" placeholder="Escribe el nombre del segundo"
                                           value="{{ old('second_names.' . $i, $secondNames[$i] ?? '') }}">
                                </div>
                            </div>
                        @endfor
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Ver menú virtual
                    </a>
                    <a href="{{ route('restaurant.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode"></i> QR del Menú Virtual</h3>
            </div>
            <div class="card-body text-center">
                <img src="{{ $qr }}" alt="QR del Menú Virtual" style="width:100%; max-width:240px; height:auto;">
                <div class="text-muted small mt-2 text-break">{{ $publicUrl }}</div>
                <div class="mt-3">
                    <a href="{{ $qr }}" download="menu-virtual-qr.png" class="btn btn-primary">
                        <i class="fas fa-download"></i> Descargar QR
                    </a>
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                        <i class="fas fa-external-link-alt"></i> Abrir menú virtual
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
