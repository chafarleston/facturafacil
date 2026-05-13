@extends('layouts.admin')
@section('title', 'Roles')
@section('page_title', 'Roles')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Roles</h3>
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm float-right">Nuevo Rol</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Permisos</th>
                    <th>Usuarios</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td><span class="badge badge-secondary">{{ $role->slug }}</span></td>
                    <td>{{ $role->description ?: '-' }}</td>
                    <td><span class="badge badge-info">{{ $role->permissions->count() }}</span></td>
                    <td><span class="badge badge-primary">{{ $role->users->count() }}</span></td>
                    <td>
                        @if($role->is_system)
                            <span class="badge badge-danger">Sistema</span>
                        @elseif($role->status)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        @if(!$role->is_system)
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar rol?')"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection