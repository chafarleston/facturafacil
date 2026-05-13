@extends('layouts.admin')
@section('title', 'Editar Rol')
@section('page_title', 'Editar Rol')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Rol: {{ $role->name }}</h3>
    </div>
    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="name" value="{{ $role->name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="description" class="form-control" rows="3">{{ $role->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="status" class="custom-control-input" id="status" value="1" {{ $role->status ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Permisos</label>
                        <div class="permissions-grid" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                            @foreach($groupedPermissions as $module => $permissions)
                            <div class="module-section mb-3">
                                <h6 class="text-primary font-weight-bold">{{ $module }}</h6>
                                @foreach($permissions as $permission)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="permissions[]" class="custom-control-input" id="perm_{{ $permission->id }}" value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection