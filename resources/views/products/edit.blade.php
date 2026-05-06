@extends('layouts.admin')
@section('title', 'Editar Producto')
@section('page_title', 'Editar Producto')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Producto</h3>
    </div>
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código Interno</label>
                        <input type="text" name="codigo" value="{{ $product->codigo }}" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Código SUNAT</label>
                        <select name="codigo_sunat" class="form-control">
                            <option value="">Seleccionar...</option>
                            @foreach(\App\Models\SunatProduct::orderBy('descripcion')->get() as $sunat)
                                <option value="{{ $sunat->codigo }}" {{ $product->codigo_sunat == $sunat->codigo ? 'selected' : '' }}>{{ $sunat->codigo }} - {{ $sunat->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="descripcion" value="{{ $product->descripcion }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="category_id" class="form-control">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" value="{{ $product->stock ?? 0 }}" min="0">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Unitario (Con IGV)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" id="precio_con_igv" name="precio_con_igv" value="{{ $product->precio }}" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Unitario (Sin IGV)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" id="precio_sin_igv" name="precio_sin_igv" value="{{ number_format($product->precio / 1.18, 2) }}" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select name="umedida_codigo" class="form-control">
                            <option value="NIU" {{ $product->umedida_codigo == 'NIU' ? 'selected' : '' }}>Unidad (NIU)</option>
                            <option value="KGM" {{ $product->umedida_codigo == 'KGM' ? 'selected' : '' }}>Kilogramo (KGM)</option>
                            <option value="GRM" {{ $product->umedida_codigo == 'GRM' ? 'selected' : '' }}>Gramo (GRM)</option>
                            <option value="LTR" {{ $product->umedida_codigo == 'LTR' ? 'selected' : '' }}>Litro (LTR)</option>
                            <option value="MLT" {{ $product->umedida_codigo == 'MLT' ? 'selected' : '' }}>Mililitro (MLT)</option>
                            <option value="MTK" {{ $product->umedida_codigo == 'MTK' ? 'selected' : '' }}>Metro cuadrado (MTK)</option>
                            <option value="MTQ" {{ $product->umedida_codigo == 'MTQ' ? 'selected' : '' }}>Metro cúbico (MTQ)</option>
                            <option value="HR" {{ $product->umedida_codigo == 'HR' ? 'selected' : '' }}>Hora (HR)</option>
                            <option value="D" {{ $product->umedida_codigo == 'D' ? 'selected' : '' }}>Día (D)</option>
                            <option value="TNE" {{ $product->umedida_codigo == 'TNE' ? 'selected' : '' }}>Tonelada (TNE)</option>
                            <option value="BX" {{ $product->umedida_codigo == 'BX' ? 'selected' : '' }}>Caja (BX)</option>
                            <option value="PK" {{ $product->umedida_codigo == 'PK' ? 'selected' : '' }}>Paquete (PK)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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