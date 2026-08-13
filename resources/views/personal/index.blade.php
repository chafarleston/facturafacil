@extends('layouts.admin')
@section('title', 'Personal')
@section('page_title', 'Personal')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('personal.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Trabajador</a>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Trabajadores</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>DNI</th>
                    <th>Foto</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Cargo</th>
                    <th>Sueldo</th>
                    <th>Horario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personal as $p)
                <tr>
                    <td>{{ $p->dni }}</td>
                    <td>
                        @if($p->foto_url)
                            <img src="{{ $p->foto_url }}" style="width:38px; height:38px; object-fit:cover; border-radius:50%;">
                            <div>
                                @if($p->has_face)
                                    <span class="badge badge-success">Rostro</span>
                                @else
                                    <span class="badge badge-warning">Sin rostro</span>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $p->nombres }}</td>
                    <td>{{ $p->apellidos }}</td>
                    <td>{{ $p->cargo ?? '-' }}</td>
                    <td>S/ {{ number_format($p->sueldo, 2) }}</td>
                    <td>{{ $p->schedule?->name ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $p->estado == 'ACTIVO' ? 'success' : 'secondary' }}">{{ $p->estado }}</span>
                        @if($p->suspendido)
                            <span class="badge badge-danger">SUSPENDIDO</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('personal.edit', $p) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        @if($p->suspendido)
                            <form action="{{ route('personal.toggleSuspension', $p) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm" title="Levantar suspensión"><i class="fas fa-check-circle"></i></button>
                            </form>
                        @else
                            <form action="{{ route('personal.toggleSuspension', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Suspender a este trabajador?')">
                                @csrf
                                <button class="btn btn-secondary btn-sm" title="Suspender"><i class="fas fa-ban"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('personal.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este trabajador y sus registros?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center">No hay trabajadores registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
