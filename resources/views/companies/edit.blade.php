@extends('layouts.admin')
@section('title', 'Editar Empresa')
@section('page_title', 'Editar Empresa')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Empresa</h3>
    </div>
    <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" name="ruc" value="{{ $company->ruc }}" class="form-control" required maxlength="11">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Contribuyente (SUNAT)</label>
                        <select name="tipo_contribuyente" class="form-control">
                            <option value="">-- Seleccionar --</option>
                            <option value="RIESGO" {{ (string)$company->tipo_contribuyente == 'RIESGO' ? 'selected' : '' }}>RIESGO</option>
                            <option value="MYPES" {{ (string)$company->tipo_contribuyente == 'MYPES' ? 'selected' : '' }}>MYPES</option>
                            <option value="OTROS" {{ (string)$company->tipo_contribuyente == 'OTROS' ? 'selected' : '' }}>OTROS</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Razón Social</label>
                <input type="text" name="razon_social" value="{{ $company->razon_social }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Nombre Comercial</label>
                <input type="text" name="nombre_comercial" value="{{ $company->nombre_comercial }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $company->direccion }}" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Departamento</label>
                        <input type="text" name="departamento" value="{{ $company->departamento }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Provincia</label>
                        <input type="text" name="provincia" value="{{ $company->provincia }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Distrito</label>
                        <input type="text" name="distrito" value="{{ $company->distrito }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ $company->telefono }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $company->email }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Logo de la Empresa</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="logo" class="custom-file-input" id="logoInput" accept="image/*">
                        <label class="custom-file-label" for="logoInput">Seleccionar imagen</label>
                    </div>
                </div>
                <small class="form-text text-muted">Formatos: JPEG, PNG, JPG, GIF, SVG. Tamaño máximo: 2MB</small>
                @if($company->logo)
                <div class="mt-2">
                    <p><strong>Logo actual:</strong></p>
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo actual" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                </div>
                @endif
                <div id="logoPreview" class="mt-2" style="display:none;">
                    <p><strong>Vista previa:</strong></p>
                    <img id="logoPreviewImg" src="" alt="Preview" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('logoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreviewImg').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush