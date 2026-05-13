@extends('layouts.admin')
@section('title', 'Permisos')
@section('page_title', 'Permisos')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Permisos</h3>
        <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm float-right">Nuevo Permiso</a>
    </div>
    <div class="card-body">
        @foreach($groupedPermissions as $module => $permissions)
        <div class="mb-4">
            <h5 class="text-primary">{{ $module }}</h5>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $permission)
                    <tr>
                        <td>{{ $permission->name }}</td>
                        <td><span class="badge badge-secondary">{{ $permission->slug }}</span></td>
                        <td>{{ $permission->description ?: '-' }}</td>
                        <td>
                            @if($permission->status)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar permiso?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>
@endsection