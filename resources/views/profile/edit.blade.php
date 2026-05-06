@extends('layouts.admin')
@section('title', 'Perfil')
@section('page_title', 'Editar Perfil')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Información del Perfil</h3>
            </div>
            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')
                <div class="card-body">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Cambiar Contraseña</h3>
            </div>
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')
                <div class="card-body">
                    <div class="form-group">
                        <label>Contraseña Actual</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label>Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Eliminar Cuenta</h3>
            </div>
            <div class="card-body">
                <p>Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente.</p>
            </div>
            <div class="card-footer">
                <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('¿Está seguro de eliminar su cuenta?');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-danger">Eliminar Cuenta</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection