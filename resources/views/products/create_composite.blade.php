@extends('layouts.admin')
@section('title', 'Nuevo Producto Compuesto')
@section('page_title', 'Nuevo Producto Compuesto')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-boxes"></i> Nuevo Producto Compuesto</h3>
    </div>
    <form method="POST" action="{{ route('products.composite.store') }}" id="compositeForm">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código Interno</label>
                        <input type="text" name="codigo" value="{{ $codigo }}" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código de Barras</label>
                        <input type="text" name="codigo_barras" class="form-control" placeholder="EAN, UPC, etc.">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Código SUNAT (Catálogo UBL 2.1)</label>
                <input type="text" id="sunat-search" placeholder="Buscar código SUNAT..." class="form-control" autocomplete="off">
                <input type="hidden" name="codigo_sunat" id="codigo_sunat" value="">
                <div id="sunat-results" class="position-absolute bg-white border rounded mt-1 p-2" style="display:none;z-index:1000;max-height:200px;overflow:auto;width:100%;"></div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" class="form-control" required placeholder="Nombre del producto compuesto">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="category_id" class="form-control">
                            <option value="">Sin categoría</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Destino KDS</label>
                        <select name="kds_destination" class="form-control">
                            <option value="cocina">KDS Cocina</option>
                            <option value="cocina2">KDS Cocina 2</option>
                            <option value="bar">KDS Bar</option>
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
                            <input type="number" name="precio" class="form-control" required step="0.01" min="0" placeholder="0.00">
                        </div>
                        <small class="text-muted">Ingrese el precio final del producto compuesto</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo Afectación IGV</label>
                        <select name="tipo_afectacion" class="form-control" required>
                            <option value="GRA">Gravado - 18%</option>
                            <option value="EXO">Exonerado - 0%</option>
                            <option value="INA">Inafecto - 0%</option>
                            <option value="EXE">Exportación - 0%</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select name="umedida_codigo" class="form-control">
                            <option value="NIU">Unidad (NIU)</option>
                            <option value="KGM">Kilogramo (KGM)</option>
                            <option value="LTR">Litro (LTR)</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <h5><i class="fas fa-puzzle-piece"></i> Componentes del Producto</h5>
            <p class="text-muted">Agregue los productos que conforman este producto compuesto</p>
            
            <div id="components-container">
                <div id="components-list"></div>
            </div>
            
            <button type="button" class="btn btn-success btn-sm mt-2" onclick="addComponent()">
                <i class="fas fa-plus"></i> Agregar Componente
            </button>

            <div id="components-error" class="text-danger mt-2" style="display:none;"></div>
        </div>
        <div class="card-footer">
            <a href="{{ route('products.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Guardar Producto Compuesto</button>
        </div>
    </form>
</div>

<template id="component-template">
    <div class="component-row border rounded p-3 mb-2 bg-light">
        <div class="row align-items-end">
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label>Producto</label>
                    <select name="components[__INDEX__][product_id]" class="form-control component-product" required>
                        <option value="">Seleccione un producto...</option>
                        @foreach($availableProducts as $p)
                            <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" data-price="{{ $p->precio }}">
                                {{ $p->descripcion }} (Stock: {{ $p->stock }} | S/ {{ number_format($p->precio, 2) }})
                            </option>
                        @endforeach
                    </select>
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

function addComponent() {
    const template = document.getElementById('component-template');
    const clone = template.content.cloneNode(true);
    
    const html = clone.querySelector('.component-row');
    html.innerHTML = html.innerHTML.replace(/__INDEX__/g, componentIndex);
    
    document.getElementById('components-list').appendChild(html);
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
        const select = row.querySelector('select[name*="[product_id]"]');
        const input = row.querySelector('input[name*="[quantity]"]');
        if (select) select.name = `components[${index}][product_id]`;
        if (input) input.name = `components[${index}][quantity]`;
    });
}

function attachEvents() {
    document.querySelectorAll('.component-product, .component-quantity').forEach(el => {
        el.removeEventListener('change', calculateTotals);
        el.removeEventListener('input', calculateTotals);
        el.addEventListener('change', calculateTotals);
        el.addEventListener('input', calculateTotals);
    });
}

function calculateTotals() {
    const rows = document.querySelectorAll('.component-row');
    rows.forEach(row => {
        const select = row.querySelector('.component-product');
        const qtyInput = row.querySelector('.component-quantity');
        const subtotalInput = row.querySelector('.component-subtotal');
        
        if (select && qtyInput && subtotalInput) {
            const selectedOption = select.options[select.selectedIndex];
            const price = parseFloat(selectedOption.dataset.price || 0);
            const qty = parseFloat(qtyInput.value || 0);
            const subtotal = price * qty;
            subtotalInput.value = subtotal.toFixed(2);
        }
    });
}

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
        const select = row.querySelector('.component-product');
        const qtyInput = row.querySelector('.component-quantity');
        
        if (!select.value) {
            hasError = true;
            errorDiv.textContent = 'Seleccione un producto para cada componente.';
        }
        
        if (!qtyInput.value || parseFloat(qtyInput.value) <= 0) {
            hasError = true;
            errorDiv.textContent = 'La cantidad debe ser mayor a 0.';
        }
        
        if (productIds.includes(select.value)) {
            hasError = true;
            errorDiv.textContent = 'No puede agregar el mismo producto dos veces como componente.';
        }
        
        productIds.push(select.value);
    });
    
    if (hasError) {
        e.preventDefault();
        errorDiv.style.display = 'block';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    addComponent();
    
    const sunatSearch = document.getElementById('sunat-search');
    const codigoSunat = document.getElementById('codigo_sunat');
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
            fetch('{{ route("sunat-products.search") }}?query=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(list => {
                    resultsBox.innerHTML = '';
                    if (list.length === 0) {
                        resultsBox.style.display = 'none';
                        return;
                    }
                    list.forEach(item => {
                        const div = document.createElement('div');
                        div.textContent = item.codigo + ' - ' + item.descripcion;
                        div.className = 'p-2 hover:bg-light cursor-pointer';
                        div.style.cursor = 'pointer';
                        div.onclick = () => {
                            sunatSearch.value = item.codigo + ' - ' + item.descripcion;
                            codigoSunat.value = item.codigo;
                            resultsBox.style.display = 'none';
                        };
                        resultsBox.appendChild(div);
                    });
                    resultsBox.style.display = 'block';
                });
        }, 200);
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
