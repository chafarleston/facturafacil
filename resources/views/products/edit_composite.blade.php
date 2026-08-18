@extends('layouts.admin')
@section('title', 'Editar Producto Compuesto')
@section('page_title', 'Editar Producto Compuesto')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-boxes"></i> Editar Producto Compuesto</h3>
    </div>
    <form method="POST" action="{{ route('products.composite.update', $product) }}" id="compositeForm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código Interno</label>
                        <input type="text" name="codigo" value="{{ $product->codigo }}" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código de Barras</label>
                        <input type="text" name="codigo_barras" value="{{ $product->codigo_barras }}" class="form-control" placeholder="EAN, UPC, etc.">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Código SUNAT (Catálogo UBL 2.1)</label>
                <input type="text" id="sunat-search" placeholder="Buscar código SUNAT..." class="form-control" autocomplete="off" value="{{ $product->codigo_sunat }}">
                <input type="hidden" name="codigo_sunat" id="codigo_sunat" value="{{ $product->codigo_sunat }}">
                <div id="sunat-results" class="position-absolute bg-white border rounded mt-1 p-2" style="display:none;z-index:1000;max-height:200px;overflow:auto;width:100%;"></div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" value="{{ $product->descripcion }}" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="category_id" class="form-control">
                            <option value="">Sin categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Destino KDS</label>
                        <select name="kds_destination" class="form-control">
                            <option value="cocina" {{ $product->kds_destination == 'cocina' ? 'selected' : '' }}>KDS Cocina</option>
                            <option value="cocina2" {{ $product->kds_destination == 'cocina2' ? 'selected' : '' }}>KDS Cocina 2</option>
                            <option value="bar" {{ $product->kds_destination == 'bar' ? 'selected' : '' }}>KDS Bar</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio de Venta (Con IGV) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" name="precio" value="{{ $product->precio }}" class="form-control" required step="0.01" min="0">
                        </div>
                        <small class="text-muted">Ingrese el precio final del producto compuesto</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo Afectación IGV</label>
                        <select name="tipo_afectacion" class="form-control" required>
                            <option value="GRA" {{ $product->tipo_afectacion == 'GRA' ? 'selected' : '' }}>Gravado - 18%</option>
                            <option value="EXO" {{ $product->tipo_afectacion == 'EXO' ? 'selected' : '' }}>Exonerado - 0%</option>
                            <option value="INA" {{ $product->tipo_afectacion == 'INA' ? 'selected' : '' }}>Inafecto - 0%</option>
                            <option value="EXE" {{ $product->tipo_afectacion == 'EXE' ? 'selected' : '' }}>Exportación - 0%</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select name="umedida_codigo" class="form-control">
                            <option value="NIU" {{ $product->umedida_codigo == 'NIU' ? 'selected' : '' }}>Unidad (NIU)</option>
                            <option value="KGM" {{ $product->umedida_codigo == 'KGM' ? 'selected' : '' }}>Kilogramo (KGM)</option>
                            <option value="LTR" {{ $product->umedida_codigo == 'LTR' ? 'selected' : '' }}>Litro (LTR)</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <h5><i class="fas fa-puzzle-piece"></i> Componentes del Producto</h5>
            <p class="text-muted">Administre los productos que conforman este producto compuesto</p>
            
            <div id="components-container">
                <div id="components-list"></div>
            </div>
            
            <button type="button" class="btn btn-success btn-sm mt-2" onclick="addComponent()">
                <i class="fas fa-plus"></i> Agregar Componente
            </button>

            <div id="components-error" class="text-danger mt-2" style="display:none;"></div>
        </div>
        <div class="card-footer">
            <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Actualizar Producto Compuesto</button>
        </div>
    </form>
</div>

<template id="component-template">
    <div class="component-row border rounded p-3 mb-2 bg-light">
        <div class="row align-items-end">
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label>Producto</label>
                    <div style="position:relative;">
                        <input type="text" class="form-control component-product-search" placeholder="Buscar producto por nombre o código..." autocomplete="off">
                        <input type="hidden" name="components[__INDEX__][product_id]" class="component-product-id" value="">
                        <div class="component-product-results" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ddd; border-radius:4px; max-height:200px; overflow-y:auto; z-index:1000;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label>Cantidad</label>
                    <input type="number" name="components[__INDEX__][quantity]" class="form-control component-quantity" required step="0.01" min="0.01" value="1">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label>Subtotal</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">S/</span>
                        </div>
                        <input type="text" class="form-control component-subtotal bg-white" readonly value="0.00">
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeComponent(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
let componentIndex = 0;
const availableProducts = @json($availableProducts);

const existingComponents = @json($product->components->map(function($c) {
    return [
        'product_id' => $c->component_product_id,
        'quantity' => $c->quantity,
    ];
}));

function addComponent(productId = null, quantity = 1) {
    const template = document.getElementById('component-template');
    const clone = template.content.cloneNode(true);

    const html = clone.querySelector('.component-row');
    html.innerHTML = html.innerHTML.replace(/__INDEX__/g, componentIndex);

    document.getElementById('components-list').appendChild(html);

    if (productId) {
        const row = html;
        const search = row.querySelector('.component-product-search');
        const hidden = row.querySelector('.component-product-id');
        const prod = availableProducts.find(p => p.id === productId);
        if (prod) {
            hidden.value = prod.id;
            search.value = prod.descripcion;
        }
    }

    if (quantity) {
        const qtyInput = row.querySelector('.component-quantity');
        if (qtyInput) qtyInput.value = quantity;
    }

    componentIndex++;

    attachEvents();
    calculateTotals();
}

function removeComponent(btn) {
    btn.closest('.component-row').remove();
    reindexComponents();
    calculateTotals();
}

function reindexComponents() {
    const rows = document.querySelectorAll('.component-row');
    rows.forEach((row, index) => {
        const hidden = row.querySelector('input[name*="[product_id]"]');
        const input = row.querySelector('input[name*="[quantity]"]');
        if (hidden) hidden.name = `components[${index}][product_id]`;
        if (input) input.name = `components[${index}][quantity]`;
    });
}

function searchComponentProduct(input) {
    const row = input.closest('.component-row');
    const results = row.querySelector('.component-product-results');
    const q = input.value.trim().toLowerCase();

    if (!q) {
        row.querySelector('.component-product-id').value = '';
        results.style.display = 'none';
        return;
    }

    const isNumeric = /^\d+$/.test(q);
    const list = availableProducts.filter(function(p) {
        if (isNumeric) return (p.codigo && String(p.codigo).toLowerCase().includes(q));
        return (p.descripcion && String(p.descripcion).toLowerCase().includes(q)) ||
               (p.codigo && String(p.codigo).toLowerCase().includes(q));
    });

    if (list.length === 0) {
        results.innerHTML = '<div style="padding:8px 12px; color:#888; font-size:13px;">Sin resultados</div>';
        results.style.display = 'block';
        return;
    }

    let html = '';
    list.slice(0, 15).forEach(function(p) {
        const price = parseFloat(p.precio || 0).toFixed(2);
        const stock = p.stock != null ? p.stock : 0;
        html += '<div style="padding:8px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #eee;" ' +
            'onclick="selectComponentProductById(this,' + p.id + ')">' +
            '<strong>' + p.descripcion + '</strong><br>' +
            '<small style="color:#888;">' + p.codigo + ' &mdash; S/ ' + price + ' &mdash; Stock: ' + stock + '</small>' +
        '</div>';
    });
    results.innerHTML = html;
    results.style.display = 'block';
}

function selectComponentProductById(el, id) {
    const prod = availableProducts.find(p => p.id === id);
    if (!prod) return;
    selectComponentProduct(el, prod.id, prod.descripcion, parseFloat(prod.precio || 0));
}

function selectComponentProduct(el, id, name, price) {
    const row = el.closest('.component-row');
    const search = row.querySelector('.component-product-search');
    const hidden = row.querySelector('.component-product-id');
    const results = row.querySelector('.component-product-results');

    hidden.value = id;
    search.value = name;
    results.style.display = 'none';
    calculateTotals();
}

function attachEvents() {
    document.querySelectorAll('.component-product-search').forEach(el => {
        el.removeEventListener('input', onSearchInput);
        el.addEventListener('input', onSearchInput);
    });
    document.querySelectorAll('.component-quantity').forEach(el => {
        el.removeEventListener('input', calculateTotals);
        el.addEventListener('input', calculateTotals);
    });
    document.querySelectorAll('.component-product-id').forEach(el => {
        el.removeEventListener('change', calculateTotals);
        el.addEventListener('change', calculateTotals);
    });
}

function onSearchInput(e) {
    searchComponentProduct(e.target);
}

function calculateTotals() {
    const rows = document.querySelectorAll('.component-row');
    rows.forEach(row => {
        const hidden = row.querySelector('.component-product-id');
        const qtyInput = row.querySelector('.component-quantity');
        const subtotalInput = row.querySelector('.component-subtotal');

        if (hidden && qtyInput && subtotalInput) {
            const id = parseInt(hidden.value);
            const prod = id ? availableProducts.find(p => p.id === id) : null;
            const price = prod ? parseFloat(prod.precio || 0) : 0;
            const qty = parseFloat(qtyInput.value || 0);
            subtotalInput.value = (price * qty).toFixed(2);
        }
    });
}

document.addEventListener('click', function(e) {
    document.querySelectorAll('.component-product-results').forEach(function(results) {
        if (!results.closest('.component-row')) return;
        if (results.closest('.component-row').contains(e.target)) return;
        results.style.display = 'none';
    });
});

document.getElementById('compositeForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.component-row');
    const errorDiv = document.getElementById('components-error');
    errorDiv.style.display = 'none';

    if (rows.length === 0) {
        e.preventDefault();
        errorDiv.textContent = 'Debe agregar al menos un componente al producto compuesto.';
        errorDiv.style.display = 'block';
        return;
    }

    const productIds = [];
    let hasError = false;

    rows.forEach(row => {
        const hidden = row.querySelector('.component-product-id');
        const qtyInput = row.querySelector('.component-quantity');

        if (!hidden || !hidden.value) {
            hasError = true;
            errorDiv.textContent = 'Seleccione un producto para cada componente.';
        }

        if (!qtyInput.value || parseFloat(qtyInput.value) <= 0) {
            hasError = true;
            errorDiv.textContent = 'La cantidad debe ser mayor a 0.';
        }

        if (hidden && productIds.includes(hidden.value)) {
            hasError = true;
            errorDiv.textContent = 'No puede agregar el mismo producto dos veces como componente.';
        }

        if (hidden) productIds.push(hidden.value);
    });

    if (hasError) {
        e.preventDefault();
        errorDiv.style.display = 'block';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (existingComponents.length > 0) {
        existingComponents.forEach(comp => {
            addComponent(comp.product_id, comp.quantity);
        });
    } else {
        addComponent();
    }
    
    const sunatSearch = document.getElementById('sunat-search');
    const resultsBox = document.getElementById('sunat-results');

    if (!sunatSearch) return;

    let timeout = null;
    sunatSearch.addEventListener('input', function() {
        const q = this.value.trim();
        if (timeout) clearTimeout(timeout);
        if (q.length < 2) {
            resultsBox.style.display = 'none';
            return;
        }
        timeout = setTimeout(() => {
            fetch('/sunat-products/search?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    resultsBox.innerHTML = '<div class="text-muted p-2">Sin resultados</div>';
                } else {
                    let html = '';
                    data.forEach(item => {
                        html += '<div class="p-2 border-bottom cursor-pointer" style="cursor:pointer" onclick="selectSunat(\'' + item.codigo + '\', \'' + (item.descripcion || '').replace(/'/g, "\\'") + '\')">' +
                            '<strong>' + item.codigo + '</strong> - ' + (item.descripcion || '') + '</div>';
                    });
                    resultsBox.innerHTML = html;
                }
                resultsBox.style.display = 'block';
            });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!sunatSearch.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });
});

function selectSunat(code, desc) {
    document.getElementById('codigo_sunat').value = code;
    document.getElementById('sunat-search').value = code + ' - ' + desc;
    document.getElementById('sunat-results').style.display = 'none';
}
</script>
@endpush
