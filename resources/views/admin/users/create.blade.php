@extends('layouts.admin')
@section('title', 'Crear Usuario')
@section('page_title', 'Crear Usuario')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo Usuario</h3>
    </div>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Rol Principal</label>
                        <select name="role" class="form-control" required>
                            <option value="user">Usuario</option>
                            <option value="admin">Administrador</option>
                            <option value="mozo">Mozo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Permisos Adicionales</label>
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                            @forelse($roles as $r)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="roles[]" class="custom-control-input" id="role_{{ $r->id }}" value="{{ $r->id }}">
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
            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection