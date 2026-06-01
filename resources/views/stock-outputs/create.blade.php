@extends('layouts.admin')
@section('title', 'Nuevo Consumo')
@section('page_title', 'Nuevo Consumo Interno')

@section('content')
<div class="card">
    <form method="POST" action="{{ route('stock-outputs.store') }}" id="consumoForm">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Motivo</label>
                        <select name="motivo" class="form-control" id="motivoSelect" required>
                            <option value="consumo_cocina">Consumo cocina</option>
                            <option value="merma">Merma</option>
                            <option value="degustacion">Degustación</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" id="motivoOtroField" style="display:none">
                    <div class="form-group">
                        <label>Especifique motivo</label>
                        <input type="text" name="motivo_otro" class="form-control" placeholder="Ej: Promoción, prueba, etc.">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Referencia (opcional)</label>
                        <input type="text" name="referencia" class="form-control" placeholder="Ej: Pedido cocina #123">
                    </div>
                </div>
            </div>

            <hr>
            <h4>Productos</h4>
            <div class="table-responsive" style="overflow:visible;">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:50%">Producto</th>
                            <th style="width:15%">Cantidad</th>
                            <th style="width:10%">U.Medida</th>
                            <th style="width:15%">Stock actual</th>
                            <th style="width:10%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        <tr class="item-row">
                            <td style="position:relative; min-width:350px;">
                                <input type="hidden" name="items[0][product_id]" class="product-id-input" required>
                                <input type="text" class="form-control product-search-input" placeholder="Escriba para buscar producto..." autocomplete="off" required>
                                <div class="product-suggestions" style="display:none; position:absolute; z-index:1000; background:#fff; border:1px solid #ddd; max-height:200px; overflow-y:auto; min-width:420px; left:0;"></div>
                            </td>
                            <td>
                                <input type="number" name="items[0][cantidad]" class="form-control cantidad" step="0.001" min="0.001" required>
                            </td>
                            <td class="umedida-cell text-center">{{ $products->first()->umedida_codigo ?? 'NIU' }}</td>
                            <td class="stock-cell text-center">0</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-success btn-sm" onclick="addRow()" title="Agregar producto">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-group mt-3">
                <label>Notas (opcional)</label>
                <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Registrar consumo</button>
            <a href="{{ route('stock-outputs.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = 1;
const products = @json($products);

document.getElementById('motivoSelect').addEventListener('change', function() {
    document.getElementById('motivoOtroField').style.display = this.value === 'otro' ? '' : 'none';
});

function initSearch(row) {
    const input = row.querySelector('.product-search-input');
    const hidden = row.querySelector('.product-id-input');
    const suggestions = row.querySelector('.product-suggestions');
    const umedidaCell = row.querySelector('.umedida-cell');
    const stockCell = row.querySelector('.stock-cell');

    input.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        hidden.value = '';
        umedidaCell.textContent = 'NIU';
        stockCell.textContent = '0';

        if (q.length < 1) {
            suggestions.style.display = 'none';
            return;
        }

        const matches = products.filter(p =>
            (p.codigo && p.codigo.toLowerCase().includes(q)) ||
            p.descripcion.toLowerCase().includes(q)
        ).slice(0, 20);

        if (matches.length === 0) {
            suggestions.style.display = 'none';
            return;
        }

        suggestions.innerHTML = '';
        matches.forEach(p => {
            const div = document.createElement('div');
            div.style.padding = '8px 12px';
            div.style.cursor = 'pointer';
            div.style.borderBottom = '1px solid #eee';
            div.textContent = (p.codigo ? p.codigo + ' - ' : '') + p.descripcion + ' (stock: ' + parseFloat(p.stock).toFixed(4) + ')';
            div.onmouseenter = function() { this.style.background = '#f0f0f0'; };
            div.onmouseleave = function() { this.style.background = '#fff'; };
            div.addEventListener('click', function() {
                input.value = (p.codigo ? p.codigo + ' - ' : '') + p.descripcion;
                hidden.value = p.id;
                umedidaCell.textContent = p.umedida_codigo || 'NIU';
                stockCell.textContent = parseFloat(p.stock).toFixed(4);
                suggestions.style.display = 'none';
            });
            suggestions.appendChild(div);
        });
        suggestions.style.display = 'block';
    });

    input.addEventListener('blur', function() {
        setTimeout(() => { suggestions.style.display = 'none'; }, 200);
    });

    input.addEventListener('focus', function() {
        if (this.value.trim().length >= 1 && !hidden.value) {
            this.dispatchEvent(new Event('input'));
        }
    });
}

function addRow() {
    const tbody = document.getElementById('itemsContainer');
    const firstRow = tbody.querySelector('.item-row');
    const clone = firstRow.cloneNode(true);

    clone.querySelectorAll('input').forEach(el => {
        const name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
        el.name = name;
        el.value = '';
    });

    clone.querySelector('.umedida-cell').textContent = 'NIU';
    clone.querySelector('.stock-cell').textContent = '0';

    const suggestions = clone.querySelector('.product-suggestions');
    if (suggestions) suggestions.innerHTML = '';

    const actions = clone.querySelector('td:last-child');
    actions.innerHTML = '';
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-danger btn-sm';
    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
    removeBtn.onclick = function() { clone.remove(); };
    actions.appendChild(removeBtn);

    initSearch(clone);
    tbody.appendChild(clone);
    rowIndex++;
}

initSearch(document.querySelector('.item-row'));
</script>
@endpush
