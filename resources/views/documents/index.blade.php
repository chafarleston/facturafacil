@extends('layouts.admin')
@section('title', $title)
@section('page_title', $title)

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Lista de {{ $title }}</h3>
        <div class="card-tools">
            <a href="{{ route('documents.create', $tipo) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Fecha</th>
                    <th>RUC</th>
                    <th>Razón Social</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($docs as $doc)
                <tr>
                    <td><strong>{{ $doc->full_number }}</strong></td>
                    <td>{{ $doc->fecha_emision }}</td>
                    <td>{{ $doc->entity?->num_doc ?? '—' }}</td>
                    <td>{{ $doc->entity?->razon_social ?? '—' }}</td>
                    <td>S/ {{ number_format($doc->total, 2) }}</td>
                    <td>
                        @switch($doc->sunat_estado)
                            @case('PENDIENTE')<span class="badge badge-warning">Pendiente</span>@break
                            @case('ACEPTADO')<span class="badge badge-success">Aceptado</span>@break
                            @case('RECHAZADO')<span class="badge badge-danger">Rechazado</span>@break
                            @default<span class="badge badge-secondary">{{ $doc->sunat_estado }}</span>
                        @endswitch
                    </td>
                    <td>
                        <a href="{{ route('documents.show', [$tipo, $doc]) }}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">No hay documentos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $docs->links() }}</div>
</div>
@endsection
