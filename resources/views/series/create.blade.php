@extends('layouts.admin')
@section('title', 'Nueva Serie')
@section('page_title', 'Nueva Serie')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Serie</h3>
    </div>
    <form method="POST" action="{{ route('series.store') }}">
        @csrf
        <input type="hidden" name="company_id" value="{{ $company->id }}">
        <div class="card-body">
            <div class="form-group">
                <label>Tipo de Documento</label>
                <select name="tipo_documento" class="form-control" required id="tipoDocumento">
                    <option value="01">Factura Electrónica</option>
                    <option value="03">Boleta de Venta Electrónica</option>
                    <option value="NV">Nota de Venta</option>
                    <option value="07">Nota de Crédito</option>
                    <option value="08">Nota de Débito</option>
                    <option value="09">Guía de Remisión</option>
                    <option value="20">Retención</option>
                    <option value="40">Percepción</option>
                </select>
                <small class="text-muted">F001 (Factura) | B001 (Boleta) | NV01 (NV) | FC01/BC01 (NC) | FD01/BD01 (ND) | T001 (Guía) | R001 (Retención) | P001 (Percepción)</small>
            </div>
            <div class="form-group">
                <label>Número de Serie</label>
                <input type="text" name="serie" class="form-control" placeholder="Ej: F001, B001, NV01" maxlength="4" required id="serieInput">
                <small class="text-muted">Formato: F001 (Factura), B001 (Boleta), NV01 (Nota de Venta)</small>
            </div>
            <div class="form-group">
                <label>Número de Inicio</label>
                <input type="number" name="numero_inicio" class="form-control" value="1" min="1" required>
                <small class="text-muted">El primer documento comenzará desde este número</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Crear Serie</button>
            <a href="{{ route('series.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('tipoDocumento').addEventListener('change', function() {
    var serieInput = document.getElementById('serieInput');
    var tipo = this.value;
    var placeholders = {
        '01': 'Ej: F001',
        '03': 'Ej: B001',
        'NV': 'Ej: NV01',
        '07': 'Ej: FC01 (Factura) o BC01 (Boleta)',
        '08': 'Ej: FD01 (Factura) o BD01 (Boleta)'
    };
    serieInput.placeholder = placeholders[tipo] || 'Ej: F001';
});
</script>
@endpush