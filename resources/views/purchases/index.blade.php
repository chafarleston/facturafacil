@extends('layouts.admin')
@section('title', 'Compras')
@section('page_title', 'Compras')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Compras</h3>
        <a href="{{ route('purchases.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm float-right">Nueva Compra</a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Documento</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->fecha }}</td>
                    <td>{{ $purchase->tipo_documento }} - {{ $purchase->numero_documento }}</td>
                    <td>{{ $purchase->supplier->nombre ?? 'Sin proveedor' }}</td>
                    <td>S/ {{ number_format($purchase->total, 2) }}</td>
                    <td><span class="badge badge-{{ $purchase->estado == 'REGISTRADO' ? 'success' : 'danger' }}">{{ $purchase->estado }}</span></td>
                    <td>
                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        @if($purchase->estado == 'REGISTRADO')
                        <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Anular compra? Esto restará el stock.')"><i class="fas fa-times"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">No hay compras</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="card-footer">{{ $purchases->links() }}</div>
    </div>
</div>
@endsection