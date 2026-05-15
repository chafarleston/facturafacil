@extends('layouts.admin')
@section('title', 'Cola de Impresión')
@section('page_title', 'Cola de Impresión')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Trabajos de Impresión</h3>
                <div class="card-tools">
                    <span class="badge badge-info mr-2">Pendientes: {{ $jobs->where('status', 'pending')->count() }}</span>
                    <span class="badge badge-success mr-2">Completados: {{ $jobs->where('status', 'completed')->count() }}</span>
                    <span class="badge badge-danger">Fallidos: {{ $jobs->where('status', 'failed')->count() }}</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Impresora</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Creado</th>
                            <th>Error</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                        <tr>
                            <td>{{ $job->id }}</td>
                            <td>
                                <span class="badge badge-{{ $job->job_type == 'invoice' ? 'primary' : ($job->job_type == 'prebill' ? 'info' : 'secondary') }}">
                                    {{ $job->job_type }}
                                </span>
                            </td>
                            <td>
                                @if($job->type === 'network')
                                    {{ $job->printer_ip }}:{{ $job->printer_port }}
                                @else
                                    {{ $job->printer_name ?? '—' }}
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $job->status == 'completed' ? 'success' : ($job->status == 'failed' ? 'danger' : ($job->status == 'processing' ? 'warning' : 'secondary')) }}">
                                    {{ $job->status }}
                                </span>
                            </td>
                            <td>{{ $job->attempts }}</td>
                            <td>{{ $job->created_at->format('H:i:s') }}</td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;font-size:11px;">
                                {{ Str::limit($job->error_message, 40) ?? '—' }}
                            </td>
                            <td>
                                @if($job->status === 'failed')
                                <form action="{{ route('printers.queue.retry', $job) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-info"><i class="fas fa-redo"></i></button>
                                </form>
                                @endif
                                <form action="{{ route('printers.queue.destroy', $job) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay trabajos de impresión</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
