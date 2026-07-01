@extends('layouts.admin')
@section('title', 'Resúmenes Diarios')
@section('page_title', 'Resúmenes Diarios SUNAT')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-invoice"></i> Resúmenes de Boletas, NC y ND
        </h3>
        <div class="card-tools">
            @if($pendingCount > 0)
            <form action="{{ route('sunat-summaries.checkAll') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Consultar todos los resúmenes pendientes?')">
                    <i class="fas fa-sync"></i> Consultar Pendientes ({{ $pendingCount }})
                </button>
            </form>
            @endif
            <form action="{{ route('sunat-summaries.sendDaily') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Enviar resumen diario de boletas pendientes?')">
                    <i class="fas fa-file-export"></i> Enviar Resumen Diario
                </button>
            </form>
            <form action="{{ route('sunat-summaries.retryPending') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-info btn-sm" title="Reenviar facturas/boletas pendientes a SUNAT">
                    <i class="fas fa-redo"></i> Reenviar Pendientes
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('sunat-summaries.index') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-default' }}">Todos</a>
            <a href="{{ route('sunat-summaries.index', ['status' => 'ENVIADO']) }}" class="btn btn-sm {{ $status === 'ENVIADO' ? 'btn-info' : 'btn-default' }}">Pendientes</a>
            <a href="{{ route('sunat-summaries.index', ['status' => 'ACEPTADO']) }}" class="btn btn-sm {{ $status === 'ACEPTADO' ? 'btn-success' : 'btn-default' }}">Aceptados</a>
            <a href="{{ route('sunat-summaries.index', ['status' => 'RECHAZADO']) }}" class="btn btn-sm {{ $status === 'RECHAZADO' ? 'btn-danger' : 'btn-default' }}">Rechazados</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Correlativo</th>
                        <th>Ticket</th>
                        <th>F. Emisión</th>
                        <th>F. Operación</th>
                        <th>Documentos</th>
                        <th>Estado</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaries as $summary)
                    <tr>
                        <td><code>{{ $summary->correlativo }}</code></td>
                        <td><small>{{ $summary->ticket ?? '—' }}</small></td>
                        <td>{{ $summary->fecha_emision }}</td>
                        <td>{{ $summary->fecha_operacion }}</td>
                        <td>{{ $summary->cantidad_documentos }}</td>
                        <td>
                            @switch($summary->sunat_estado)
                                @case('PENDIENTE')
                                <span class="badge badge-secondary">Pendiente</span>
                                @break
                                @case('ENVIADO')
                                <span class="badge badge-info">Enviado</span>
                                @break
                                @case('ACEPTADO')
                                <span class="badge badge-success">Aceptado</span>
                                @break
                                @case('RECHAZADO')
                                <span class="badge badge-danger">Rechazado</span>
                                @break
                                @default
                                <span class="badge badge-secondary">{{ $summary->sunat_estado }}</span>
                            @endswitch
                        </td>
                        <td>{{ $summary->updated_at ? $summary->updated_at->format('d/m H:i') : '—' }}</td>
                        <td>
                            @if(in_array($summary->sunat_estado, ['PENDIENTE', 'ENVIADO']) && $summary->ticket)
                            <form action="{{ route('sunat-summaries.check', $summary) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-info btn-xs" title="Consultar ticket">
                                    <i class="fas fa-sync"></i> Consultar
                                </button>
                            </form>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            No hay resúmenes diarios registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
</div>
    <div class="card-footer">
        {{ $summaries->appends(['status' => $status])->links() }}
    </div>
</div>

<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice"></i> Envíos Individuales (Facturas, NC, ND)</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Tipo</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Código</th>
                    <th>Actualizado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($individualSends ?? [] as $inv)
                <tr>
                    <td><strong>{{ $inv->full_number }}</strong></td>
                    <td>
                        @switch($inv->tipo_documento)
                            @case('01')<span class="badge badge-info">Factura</span>@break
                            @case('03')<span class="badge badge-success">Boleta</span>@break
                            @case('07')<span class="badge badge-primary">NC</span>@break
                            @case('08')<span class="badge badge-danger">ND</span>@break
                            @case('NV')<span class="badge badge-warning">NV</span>@break
                            @default<span class="badge badge-secondary">{{ $inv->tipo_documento }}</span>
                        @endswitch
                    </td>
                    <td>S/ {{ number_format($inv->total, 2) }}</td>
                    <td>
                        @switch($inv->sunat_estado)
                            @case('PENDIENTE')<span class="badge badge-warning">Pendiente</span>@break
                            @case('ENVIADO')<span class="badge badge-info">Enviado</span>@break
                            @case('ACEPTADO')<span class="badge badge-success">Aceptado</span>@break
                            @case('RECHAZADO')<span class="badge badge-danger">Rechazado</span>@break
                            @case('ANULADO')<span class="badge badge-secondary">Anulado</span>@break
                            @default<span class="badge badge-secondary">{{ $inv->sunat_estado }}</span>
                        @endswitch
                    </td>
                    <td><small>{{ $inv->sunat_code ?? '—' }} {{ $inv->sunat_description ? '- ' . \Illuminate\Support\Str::limit($inv->sunat_description, 30) : '' }}</small></td>
                    <td>{{ $inv->updated_at ? $inv->updated_at->format('d/m H:i') : '—' }}</td>
                    <td>
                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                        @if(in_array($inv->sunat_estado, ['PENDIENTE', 'RECHAZADO']) && $inv->tipo_documento != 'NV')
                        <a href="{{ route('invoices.send', $inv) }}" class="btn btn-success btn-xs" title="Enviar a SUNAT"><i class="fas fa-paper-plane"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No hay envíos individuales registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
