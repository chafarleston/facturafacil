@extends('layouts.admin')
@section('title', 'Caja')
@section('page_title', 'Caja')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if($cajaAbierta)
        <div class="alert alert-success">
            <h4><i class="fas fa-cash-register"></i> Caja Abierta</h4>
            <p>Fecha apertura: {{ $cajaAbierta->fecha_apertura->format('d/m/Y H:i') }}</p>
            <p>Monto apertura: S/ {{ number_format($cajaAbierta->monto_apertura, 2) }}</p>
            <form method="POST" action="{{ route('cashregisters.close') }}" class="mt-3">
                @csrf
                <input type="hidden" name="cashregister_id" value="{{ $cajaAbierta->id }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Monto de cierre</label>
                            <input type="number" name="monto_cierre" class="form-control" step="0.01" placeholder="S/ total en caja">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="1" placeholder="Notas adicionales"></textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Cerrar caja? Se generará el resumen de ventas.')">
                    <i class="fas fa-lock"></i> Cerrar Caja
                </button>
            </form>
        </div>
        @else
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Abrir Caja</h3>
            </div>
            <form method="POST" action="{{ route('cashregisters.open') }}">
                @csrf
                <input type="hidden" name="company_id" value="{{ $companyId }}">
                <div class="card-body">
                    <div class="form-group">
                        <label>Monto de apertura (efectivo inicial)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" name="monto_apertura" class="form-control" step="0.01" min="0" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-cash-register"></i> Abrir Caja</button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de Cajas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Ventas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cajas as $caja)
                        <tr>
                            <td>{{ $caja->fecha_apertura->format('d/m/Y') }}</td>
                            <td>{{ $caja->user->name }}</td>
                            <td>S/ {{ number_format($caja->monto_apertura, 2) }}</td>
                            <td>{{ $caja->monto_cierre ? 'S/ ' . number_format($caja->monto_cierre, 2) : '-' }}</td>
                            <td>{{ $caja->cantidad_ventas }}</td>
                            <td>S/ {{ number_format($caja->total_ventas, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $caja->estado == 'ABIERTA' ? 'success' : 'secondary' }}">
                                    {{ $caja->estado }}
                                </span>
                            </td>
                            <td>
                                @if($caja->estado === 'CERRADA')
                                <a href="{{ route('cashregisters.show', $caja) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('cashregisters.pdf', $caja) }}" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> A4</a>
                                <a href="{{ route('cashregisters.ticket', $caja) }}" class="btn btn-warning btn-sm" target="_blank"><i class="fas fa-print"></i> 80mm</a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">No hay cajas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="card-footer">{{ $cajas->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection