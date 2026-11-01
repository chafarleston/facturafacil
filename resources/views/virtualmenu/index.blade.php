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
                        Si dejas un segundo vacío, ese menú se mostrará solo con el plato principal.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-utensils"></i> Platos de menú (5)</h5>
                            @for($i = 0; $i < 5; $i++)
                                <div class="form-group">
                                    <label for="menu_product_{{ $i }}">Menú {{ $i + 1 }}</label>
                                    <select name="menu_products[]" id="menu_product_{{ $i }}" class="form-control">
                                        <option value="">— Seleccionar plato —</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                @selected((string) old('menu_products.' . $i, $menuIds[$i] ?? '') === (string) $product->id)>
                                                {{ $product->descripcion }} — S/ {{ number_format($product->precio, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endfor
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-bowl-rice"></i> Segundos (4)</h5>
                            @for($i = 0; $i < 4; $i++)
                                <div class="form-group">
                                    <label for="second_product_{{ $i }}">Segundo {{ $i + 1 }}</label>
                                    <select name="second_products[]" id="second_product_{{ $i }}" class="form-control">
                                        <option value="">— Seleccionar segundo —</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                @selected((string) old('second_products.' . $i, $secondIds[$i] ?? '') === (string) $product->id)>
                                                {{ $product->descripcion }} — S/ {{ number_format($product->precio, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endfor
                        </div>
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

    </div>
</div>
@endsection
