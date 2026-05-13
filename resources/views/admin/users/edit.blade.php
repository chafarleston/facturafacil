@extends('layouts.admin')
@section('title', 'Editar Usuario')
@section('page_title', 'Editar Usuario')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Usuario: {{ $user->name }}</h3>
    </div>
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="name" value="{{ $user->name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Rol Principal</label>
                        <select name="role" class="form-control" required>
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="mozo" {{ $user->role === 'mozo' ? 'selected' : '' }}>Mozo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Permisos Adicionales</label>
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                            @forelse($roles as $r)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="roles[]" class="custom-control-input" id="role_{{ $r->id }}" value="{{ $r->id }}" {{ in_array($r->id, $userRoles) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="role_{{ $r->id }}">{{ $r->name }}</label>
                            </div>
                            @empty
                            <p class="text-muted">No hay roles disponibles</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection