@extends('layouts.admin')
@section('title', 'Ingresos y Gastos')
@section('page_title', 'Ingresos y Gastos')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Ingresos y Gastos de Caja</h3>
                @if($cajaAbierta)
                <span class="badge badge-success ml-2">Caja abierta #{{ $cajaAbierta->id }} @if($cajaAbierta->referencia) ({{ $cajaAbierta->referencia }}) @endif</span>
                @else
                <span class="badge badge-danger ml-2">Sin caja abierta</span>
                @endif
            </div>
            <div class="card-body">
                @if($cajaAbierta)
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Apertura</span>
                                <span class="info-box-number">S/ {{ number_format($cajaAbierta->monto_apertura, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-shopping-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ventas</span>
                                <span class="info-box-number">S/ {{ number_format($cajaAbierta->total_ventas, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-arrow-down"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ingresos</span>
                                <span class="info-box-number">S/ {{ number_format($cajaAbierta->total_ingresos, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Egresos</span>
                                <span class="info-box-number">S/ {{ number_format($cajaAbierta->total_egresos, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calculator"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Saldo en caja</span>
                                <span class="info-box-number">S/ {{ number_format(($cajaAbierta->monto_apertura + $cajaAbierta->total_ventas + $cajaAbierta->total_ingresos - $cajaAbierta->total_egresos), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('cash-movements.store') }}" class="mb-4">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $companyId }}">
                    <div class="row">
                        <div class="col-md-2">
                            <select name="tipo" class="form-control" required>
                                <option value="INGRESO">Ingreso</option>
                                <option value="EGRESO">Egreso</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="motivo" class="form-control" placeholder="Motivo (ej: Retiro para compras, Pago proveedor, Abono...)" maxlength="255" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="monto" class="form-control" placeholder="Monto S/" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus"></i> Registrar</button>
                        </div>
                    </div>
                    @error('monto')<small class="text-danger">{{ $message }}</small>@enderror
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th class="text-right">Monto</th>
                                <th>Registrado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimientos as $mov)
                            <tr>
                                <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($mov->tipo === 'INGRESO')
                                    <span class="badge badge-success">Ingreso</span>
                                    @else
                                    <span class="badge badge-danger">Egreso</span>
                                    @endif
                                </td>
                                <td>{{ $mov->motivo }}</td>
                                <td class="text-right {{ $mov->tipo === 'INGRESO' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold' }}">
                                    {{ $mov->tipo === 'INGRESO' ? '+' : '-' }} S/ {{ number_format($mov->monto, 2) }}
                                </td>
                                <td>{{ $mov->user?->name ?? 'Eliminado' }}</td>
                                <td>
                                    <form action="{{ route('cash-movements.destroy', $mov) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('¿Anular este movimiento?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">No hay movimientos registrados en esta caja</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-cash-register" style="font-size:60px; color:#dc3545;"></i>
                    <h4 class="mt-3">Caja no aperturada</h4>
                    <p class="text-muted">Para registrar ingresos o gastos es necesario tener una caja abierta.<br>El <b>Administrador</b> o el <b>Cajero</b> deben aperturarla.</p>
                    @if(auth()->user()->hasPermission('view_cashregisters') || auth()->user()->hasPermission('open_cashregister'))
                    <a href="{{ route('cashregisters.index') }}" class="btn btn-primary">Ir a Caja</a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
