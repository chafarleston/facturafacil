@extends('layouts.admin')
@section('title', 'Horarios')
@section('page_title', 'Horarios de Trabajo')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('schedules.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Horario</a>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title">Lista de Horarios</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Entrada 1</th>
                    <th>Salida 1</th>
                    <th>Entrada 2</th>
                    <th>Salida 2</th>
                    <th>Tolerancia</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td><span class="badge badge-{{ $s->tipo == 'corrido' ? 'info' : 'primary' }}">{{ $s->tipo == 'corrido' ? 'Corrido' : 'Dividido' }}</span></td>
                    <td>{{ $s->entrada_1 ?? '-' }}</td>
                    <td>{{ $s->salida_1 ?? '-' }}</td>
                    <td>{{ $s->entrada_2 ?? '-' }}</td>
                    <td>{{ $s->salida_2 ?? '-' }}</td>
                    <td>{{ $s->tolerancia_1 }} min / {{ $s->tolerancia_2 }} min</td>
                    <td>
                        @if($s->dias_laborables)
                            @foreach($s->dias_laborables as $d)
                                <span class="badge badge-secondary">{{ ['L','M','X','J','V','S','D'][$d-1] }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">Todos</span>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $s->estado == 'ACTIVO' ? 'success' : 'secondary' }}">{{ $s->estado }}</span></td>
                    <td>
                        <a href="{{ route('schedules.edit', $s) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('schedules.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este horario?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center">No hay horarios configurados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
