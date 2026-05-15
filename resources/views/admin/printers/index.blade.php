@extends('layouts.admin')
@section('title', 'Impresoras')
@section('page_title', 'Gestión de Impresoras')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(!$serverRunning)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            El servidor de impresión (<strong>Print Server</strong>) no está disponible.
            Asegúrate de ejecutar <code>node server.js</code> en la carpeta <code>print-server</code>.
        </div>
        @else
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Servidor de impresión conectado correctamente.
            <a href="{{ route('printers.detect') }}" class="btn btn-sm btn-info ml-2" onclick="return confirm('¿Detectar impresoras instaladas en Windows?')">
                <i class="fas fa-search"></i> Detectar impresoras
            </a>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Impresoras registradas</h3>
                <div class="card-tools">
                    <a href="{{ route('printers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Impresora
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Impresora / IP</th>
                            <th>Asignado a</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($printers as $printer)
                        <tr>
                            <td>{{ $printer->name }}</td>
                            <td>
                                <span class="badge badge-{{ $printer->type == 'local' ? 'info' : 'warning' }}">
                                    {{ $printer->type == 'local' ? 'Local' : 'Red' }}
                                </span>
                            </td>
                            <td>
                                @if($printer->type == 'local')
                                    {{ $printer->printer_name }}
                                @else
                                    {{ $printer->ip_address }}:{{ $printer->port }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $labels = ['cocina-1' => 'KDS Cocina 1', 'cocina-2' => 'KDS Cocina 2', 'bar-1' => 'KDS Bar 1', 'bar-2' => 'KDS Bar 2', 'pos' => 'Punto de Venta'];
                                @endphp
                                {{ $labels[$printer->assigned_to] ?? 'Sin asignar' }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $printer->active ? 'success' : 'secondary' }}">
                                    {{ $printer->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('printers.edit', $printer) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('printers.destroy', $printer) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('¿Eliminar impresora?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay impresoras registradas.
                                <a href="{{ route('printers.create') }}">Agregar primera impresora</a>
                                @if($serverRunning && count($availablePrinters) > 0)
                                o <a href="{{ route('printers.detect') }}">detectar impresoras</a>.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($serverRunning && count($availablePrinters) > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Impresoras detectadas en Windows</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($availablePrinters as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-print"></i> {{ $p['name'] }}</span>
                        <span class="text-muted small">{{ $p['status'] ?? ($p['isDefault'] ? 'Predeterminada' : 'Disponible') }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
