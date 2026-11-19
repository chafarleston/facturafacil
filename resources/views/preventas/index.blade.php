@extends('layouts.admin')
@section('title', 'Preventas')
@section('page_title', 'Preventas')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span>&times;</span></button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-handshake"></i> {{ $tipos[$tipo] }}</h3>
                <div class="card-tools">
                    <a href="{{ route('preventas.create', ['tipo' => $tipo]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo registro
                    </a>
                </div>
            </div>
            <div class="card-body pb-0">
                <ul class="nav nav-tabs mb-3">
                    @foreach($tipos as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $tipo === $key ? 'active' : '' }}"
                               href="{{ route('preventas.index', ['tipo' => $key]) }}">
                                {{ $label }}
                                <span class="badge badge-light ml-1">{{ $conteos[$key] ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form method="GET" action="{{ route('preventas.index') }}" class="form-inline mb-3">
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    <input type="text" name="search" class="form-control form-control-sm mr-2"
                           value="{{ $search }}" placeholder="Buscar por número, cliente o título...">
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Buscar</button>
                    @if($search !== '')
                        <a href="{{ route('preventas.index', ['tipo' => $tipo]) }}" class="btn btn-link btn-sm">Limpiar</a>
                    @endif
                </form>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Título</th>
                            <th>Fecha</th>
                            <th>Vencimiento</th>
                            <th class="text-right">Monto</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preventas as $preventa)
                            @php
                                $estadoClass = match($preventa->estado) {
                                    'APROBADO', 'VIGENTE', 'ATENDIDO', 'ENTREGADO' => 'success',
                                    'RECHAZADO', 'CANCELADO' => 'danger',
                                    'EN_PROCESO', 'ENVIADO' => 'info',
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td><strong>{{ $preventa->numero }}</strong></td>
                                <td>
                                    {{ $preventa->cliente_nombre }}
                                    @if($preventa->cliente_documento)
                                        <br><small class="text-muted">{{ $preventa->cliente_documento }}</small>
                                    @endif
                                </td>
                                <td>{{ $preventa->titulo }}</td>
                                <td>{{ $preventa->fecha?->format('d/m/Y') }}</td>
                                <td>{{ $preventa->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-right">
                                    {{ $preventa->monto !== null ? 'S/ ' . number_format((float) $preventa->monto, 2) : '-' }}
                                </td>
                                <td><span class="badge badge-{{ $estadoClass }}">{{ $preventa->estadoLabel() }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('preventas.show', $preventa) }}" class="btn btn-info btn-xs" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('preventas.edit', $preventa) }}" class="btn btn-warning btn-xs" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('preventas.destroy', $preventa) }}" method="POST" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No hay registros en {{ $tipos[$tipo] }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($preventas->hasPages())
                <div class="card-footer clearfix">
                    {{ $preventas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
