@extends('layouts.admin')
@section('title', 'Consumo Interno')
@section('page_title', 'Consumo Interno')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Historial de Consumos</h3>
        <a href="{{ route('stock-outputs.create', ['company_id' => $companyId ?? null]) }}" class="btn btn-primary btn-sm float-right">
            <i class="fas fa-plus"></i> Nuevo Consumo
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                    <th>Referencia</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outputs as $output)
                <tr style="{{ $output->trashed() ? 'background-color:#f8d7da; text-decoration:line-through; opacity:0.7;' : '' }}">
                    <td>{{ $output->id }}</td>
                    <td>{{ $output->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $output->user->name ?? '-' }}</td>
                    <td>
                        @php
                            $motivos = [
                                'consumo_cocina' => 'Consumo cocina',
                                'merma' => 'Merma',
                                'degustacion' => 'Degustación',
                                'otro' => $output->motivo_otro ?? 'Otro',
                            ];
                        @endphp
                        <span class="badge {{ $output->trashed() ? 'badge-danger' : 'badge-info' }}">
                            {{ $output->trashed() ? 'ANULADO' : $motivos[$output->motivo] ?? $output->motivo }}
                        </span>
                    </td>
                    <td>{{ $output->referencia ?? '-' }}</td>
                    <td>{{ $output->items->count() }} productos</td>
                    <td>
                        <a href="{{ route('stock-outputs.show', $output) }}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('stock-outputs.print.a4', $output) }}" class="btn btn-secondary btn-xs" target="_blank" title="Imprimir A4">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="{{ route('stock-outputs.print.ticket', $output) }}" class="btn btn-secondary btn-xs" target="_blank" title="Imprimir Ticket 80mm">
                            <i class="fas fa-receipt"></i>
                        </a>
                        @if(!$output->trashed())
                        <a href="{{ route('stock-outputs.edit', $output) }}" class="btn btn-warning btn-xs">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('stock-outputs.destroy', $output) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Anular este consumo? El stock se reincorporará automáticamente.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">No hay consumos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="card-footer">{{ $outputs->links() }}</div>
    </div>
</div>
@endsection
