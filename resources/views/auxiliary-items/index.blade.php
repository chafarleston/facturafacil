@extends('layouts.admin')
@section('title', 'Elementos Auxiliares')
@section('page_title', 'Elementos Auxiliares')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de elementos</h3>
        <a href="{{ route('auxiliary-items.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm float-right">
            <i class="fas fa-plus"></i> Nuevo Elemento
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>
                        <span class="badge {{ $item->status == 'ACTIVO' ? 'badge-success' : 'badge-secondary' }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('auxiliary-items.edit', $item) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('auxiliary-items.destroy', $item) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este elemento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No hay elementos auxiliares registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
