@extends('layouts.admin')
@section('title', 'Editar Categoría')
@section('page_title', 'Editar Categoría')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Categoría</h3>
    </div>
    <form method="POST" action="{{ route('categories.update', $category) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
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
                    <div id="imagenPreviewWrap" class="{{ $category->imagen ? '' : 'd-none' }}">
                        <img id="imagenPreview" src="{{ $category->imagen_url }}" style="max-height:90px;border:1px solid #ddd;padding:4px;border-radius:4px;">
                        @if($category->imagen)
                        <div class="custom-control custom-checkbox mt-1">
                            <input type="checkbox" name="remove_imagen" value="1" class="custom-control-input" id="removeImagen">
                            <label class="custom-control-label text-danger" for="removeImagen">Eliminar imagen</label>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="{{ $category->nombre }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ $category->descripcion }}</textarea>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="ACT" {{ $category->estado == 'ACT' ? 'selected' : '' }}>Activo</option>
                    <option value="INA" {{ $category->estado == 'INA' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('categories.index', ['company_id' => $category->company_id]) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
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