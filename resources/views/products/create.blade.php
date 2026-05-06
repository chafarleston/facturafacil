@extends('layouts.admin')
@section('title', 'Nuevo Producto')
@section('page_title', 'Nuevo Producto')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Producto</h3>
    </div>
    <form method="POST" action="{{ route('products.store') }}">
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
                        <label>Código SUNAT (Catálogo UBL 2.1)</label>
                        <input type="text" id="sunat-search" placeholder="Buscar código SUNAT..." class="form-control" autocomplete="off">
                        <input type="hidden" name="codigo_sunat" id="codigo_sunat" value="">
                        <div id="sunat-results" class="position-absolute bg-white border rounded mt-1 p-2" style="display:none;z-index:1000;max-height:200px;overflow:auto;width:100%;"></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="descripcion" class="form-control" required placeholder="Nombre del producto">
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="category_id" class="form-control">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Stock Inicial</label>
                <input type="number" name="stock" class="form-control" value="0" min="0" placeholder="0">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Unitario (Sin IGV)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" id="precio_sin_igv" name="precio_sin_igv" class="form-control" required step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Unitario (Con IGV)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" id="precio_con_igv" name="precio_con_igv" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select name="umedida_codigo" class="form-control">
                            <option value="NIU">Unidad (NIU)</option>
                            <option value="KGM">Kilogramo (KGM)</option>
                            <option value="GRM">Gramo (GRM)</option>
                            <option value="LTR">Litro (LTR)</option>
                            <option value="MLT">Mililitro (MLT)</option>
                            <option value="MTK">Metro cuadrado (MTK)</option>
                            <option value="MTQ">Metro cúbico (MTQ)</option>
                            <option value="HR">Hora (HR)</option>
                            <option value="D">Día (D)</option>
                            <option value="TNE">Tonelada (TNE)</option>
                            <option value="BX">Caja (BX)</option>
                            <option value="PK">Paquete (PK)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('products.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    sunatSearch.addEventListener('blur', () => {
        setTimeout(() => { resultsBox.style.display = 'none'; }, 150);
    });

    const IGV_RATE = 1.18;
    const precioSinIgvInput = document.getElementById('precio_sin_igv');
    const precioConIgvInput = document.getElementById('precio_con_igv');
    let syncing = false;

    precioSinIgvInput.addEventListener('input', function() {
        if (syncing) return;
        const sinIgv = parseFloat(this.value) || 0;
        syncing = true;
        precioConIgvInput.value = (sinIgv * IGV_RATE).toFixed(2);
        syncing = false;
    });

    precioConIgvInput.addEventListener('input', function() {
        if (syncing) return;
        const conIgv = parseFloat(this.value) || 0;
        syncing = true;
        precioSinIgvInput.value = (conIgv / IGV_RATE).toFixed(2);
        syncing = false;
    });
});
</script>
@endpush