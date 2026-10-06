@extends('layouts.admin')
@section('title', 'Nueva Categoría')
@section('page_title', 'Nueva Categoría')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Categoría</h3>
    </div>
    <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Imagen de la Categoría</label>
                        <div class="custom-file">
                            <input type="file" name="imagen" class="custom-file-input" id="imagenInput" accept="image/*">
                            <label class="custom-file-label" for="imagenInput">Seleccionar imagen</label>
                        </div>
                        <small class="form-text text-muted">JPEG, PNG o WebP (máx. 5 MB). Se optimiza automáticamente.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="imagenPreviewWrap" class="d-none">
                        <img id="imagenPreview" src="" style="max-height:90px;border:1px solid #ddd;padding:4px;border-radius:4px;">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Servicios, Productos, etc.">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción opcional"></textarea>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT">Activo</option>
                    <option value="INA">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('categories.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('imagenInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('imagenPreview').src = ev.target.result;
        document.getElementById('imagenPreviewWrap').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});
</script>
@endpush