@extends('layouts.admin')
@section('title', 'Editar Usuario')
@section('page_title', 'Editar Usuario')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Usuario</h3>
    </div>
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" value="{{ $user->name }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection