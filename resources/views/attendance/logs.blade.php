@extends('layouts.admin')
@section('title', 'Marcaciones')
@section('page_title', 'Marcaciones de Asistencia')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Historial de Marcaciones</h3>
        <div class="card-tools">
            <form method="GET" class="form-inline">
                <select name="personal_id" class="form-control form-control-sm mr-1">
                    <option value="">Todos</option>
                    @foreach($personal as $p)
                        <option value="{{ $p->id }}" {{ $personalId == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }} ({{ $p->dni }})</option>
                    @endforeach
                </select>
                <input type="date" name="fecha" class="form-control form-control-sm mr-1" value="{{ $fecha ?? '' }}">
                <button class="btn btn-primary btn-sm">Filtrar</button>
            </form>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Trabajador</th>
                    <th>Hora</th>
                    <th>Evento</th>
                    <th>Verificación</th>
                    <th>Foto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->fecha }}</td>
                    <td>{{ $log->personal?->nombre_completo ?? 'Eliminado' }} ({{ $log->personal?->dni ?? '-' }})</td>
                    <td>{{ $log->marcado_en?->format('d/m/Y H:i:s') }}</td>
                    <td>
                        <span class="badge badge-{{ $log->tipo_evento == 'RECHAZADO' ? 'danger' : 'info' }}">{{ $log->tipo_evento }}</span>
                    </td>
                    <td>
                        @if($log->tipo_evento == 'RECHAZADO')
                            <span class="badge badge-danger" title="Distancia: {{ $log->distancia }}">Revisar</span>
                        @elseif($log->verificado === true)
                            <span class="badge badge-success" title="Distancia: {{ $log->distancia }}">Verificado</span>
                        @elseif($log->verificado === null)
                            <span class="badge badge-secondary">Sin verif.</span>
                        @else
                            <span class="badge badge-danger">Rechazado</span>
                        @endif
                    </td>
                    <td>
                        @if($log->foto_url)
                            <a href="{{ $log->foto_url }}" target="_blank"><img src="{{ $log->foto_url }}" style="width:36px; height:36px; object-fit:cover; border-radius:50%;"></a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('attendance.logs.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta marcación?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">No hay marcaciones registradas</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
