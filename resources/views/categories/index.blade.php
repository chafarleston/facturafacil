@extends('layouts.admin')
@section('title', 'Categorías')
@section('page_title', 'Categorías')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Categorías</h3>
        <a href="{{ route('categories.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm float-right">Nueva Categoría</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->nombre }}</td>
                    <td>{{ $category->descripcion }}</td>
                    <td>
                        <span class="badge badge-{{ $category->estado == 'ACT' ? 'success' : 'danger' }}">
                            {{ $category->estado }}
                        </span>
                    </td>
                    <td>{{ $category->products->count() }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar categoría?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No hay categorías</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection