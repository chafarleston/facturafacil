@extends('layouts.admin')
@section('title', 'Previsualizar Importación')
@section('page_title', 'Previsualizar Importación')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Productos a importar ({{ $total }} encontrados)
            <span class="badge badge-success ml-2">{{ $validCount }} válidos</span>
            <span class="badge badge-warning">{{ $warningCount }} con advertencias</span>
            @if($errorCount > 0)
            <span class="badge badge-danger">{{ $errorCount }} con errores</span>
            @endif
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Tipo</th>
                        <th>U.Medida</th>
                        <th>Categoría</th>
                        <th>Cód.SUNAT</th>
                        <th>KDS</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewRows as $r)
                    <tr class="{{ $r['status'] === 'error' ? 'table-danger' : ($r['status'] === 'warning' ? 'table-warning' : '') }}">
                        <td>{{ $r['row'] }}</td>
                        <td>{{ $r['codigo'] }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r['descripcion'], 40) }}</td>
                        <td>{{ $r['precio'] > 0 ? 'S/ ' . number_format($r['precio'], 2) : '-' }}</td>
                        <td>{{ $r['stock'] }}</td>
                        <td>{{ $r['tipo_afectacion'] }}</td>
                        <td>{{ $r['umedida'] }}</td>
                        <td>{{ $r['categoria'] ?: '-' }}</td>
                        <td>{{ $r['codigo_sunat'] ?: '-' }}</td>
                        <td>{{ $r['kds_destination'] }}</td>
                        <td>
                            @if($r['status'] === 'error')
                            <span class="text-danger"><i class="fas fa-times-circle"></i> {{ $r['message'] }}</span>
                            @elseif($r['status'] === 'warning')
                            <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> {{ $r['message'] }}</span>
                            @else
                            <span class="text-success"><i class="fas fa-check-circle"></i> OK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('products.import.form', ['company_id' => $companyId]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a subir
            </a>
            @if($total > 0 && $errorCount === 0)
            <form method="POST" action="{{ route('products.import.store') }}">
                @csrf
                <input type="hidden" name="company_id" value="{{ $companyId }}">
                <input type="hidden" name="tmp_file" value="{{ $tmpFile }}">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Confirmar Importación ({{ $validCount + $warningCount }} productos)
                </button>
            </form>
            @else
            <button class="btn btn-secondary" disabled title="Corrija los errores antes de importar">
                <i class="fas fa-check"></i> Confirmar Importación
            </button>
            @endif
        </div>
    </div>
</div>
@endsection
