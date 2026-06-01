@extends('layouts.admin')
@section('title', 'Editar Consumo #' . $stockOutput->id)
@section('page_title', 'Editar Consumo Interno #' . $stockOutput->id)

@section('content')
<div class="card">
    <form method="POST" action="{{ route('stock-outputs.update', $stockOutput) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Motivo</label>
                        <select name="motivo" class="form-control" id="motivoSelect" required>
                            <option value="consumo_cocina" {{ $stockOutput->motivo == 'consumo_cocina' ? 'selected' : '' }}>Consumo cocina</option>
                            <option value="merma" {{ $stockOutput->motivo == 'merma' ? 'selected' : '' }}>Merma</option>
                            <option value="degustacion" {{ $stockOutput->motivo == 'degustacion' ? 'selected' : '' }}>Degustación</option>
                            <option value="otro" {{ $stockOutput->motivo == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" id="motivoOtroField" style="{{ $stockOutput->motivo == 'otro' ? '' : 'display:none' }}">
                    <div class="form-group">
                        <label>Especifique motivo</label>
                        <input type="text" name="motivo_otro" class="form-control" value="{{ $stockOutput->motivo_otro }}" placeholder="Ej: Promoción, prueba, etc.">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Referencia (opcional)</label>
                        <input type="text" name="referencia" class="form-control" value="{{ $stockOutput->referencia }}" placeholder="Ej: Pedido cocina #123">
                    </div>
                </div>
            </div>

            <hr>
            <h4>Productos</h4>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Los productos y cantidades no se pueden modificar. Si necesita cambios, anule este consumo y cree uno nuevo.
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Stock antes</th>
                        <th>Stock después</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockOutput->items as $item)
                    <tr>
                        <td>{{ $item->product->descripcion ?? 'Producto #' . $item->product_id }}</td>
                        <td>{{ number_format($item->cantidad, 4) }}</td>
                        <td>{{ number_format($item->stock_antes, 4) }}</td>
                        <td>{{ number_format($item->stock_despues, 4) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="form-group mt-3">
                <label>Notas (opcional)</label>
                <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones...">{{ $stockOutput->notas }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('stock-outputs.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('motivoSelect').addEventListener('change', function() {
    document.getElementById('motivoOtroField').style.display = this.value === 'otro' ? '' : 'none';
});
</script>
@endpush
