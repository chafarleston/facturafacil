@extends('layouts.admin')
@section('title', 'Reportes de Asistencia')
@section('page_title', 'Reportes de Asistencia')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Filtros</h3></div>
    <div class="card-body">
        <form method="GET" class="form-inline flex-wrap">
            <select name="personal_id" class="form-control mr-2 mb-2" style="min-width:250px;">
                <option value="">Todos los trabajadores</option>
                @foreach($personal as $p)
                    <option value="{{ $p->id }}" {{ $personalId == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }} ({{ $p->dni }})</option>
                @endforeach
            </select>
            <select name="rango" class="form-control mr-2 mb-2">
                <option value="diario" {{ $rango == 'diario' ? 'selected' : '' }}>Diario</option>
                <option value="semanal" {{ $rango == 'semanal' ? 'selected' : '' }}>Semanal</option>
                <option value="mensual" {{ $rango == 'mensual' ? 'selected' : '' }}>Mensual</option>
            </select>
            <input type="date" name="fecha" class="form-control mr-2 mb-2" value="{{ $fechaRef }}">
            <input type="month" name="mes" class="form-control mr-2 mb-2" value="{{ $mes }}">
            <button class="btn btn-primary mb-2"><i class="fas fa-search"></i> Generar</button>
        </form>
        @if($report)
        <div class="mt-3">
            <a href="{{ route('attendance.reports.pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
            <a href="{{ route('attendance.reports.excel', request()->query()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
            <span class="ml-2 text-muted">Periodo: {{ $report['desde'] }} a {{ $report['hasta'] }}</span>
        </div>
        @endif
    </div>
</div>

@if($report)
@foreach($report['trabajadores'] as $t)
<div class="card">
    <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between">
            <strong>{{ $t['nombre'] }}</strong>
            <span>
                Faltas: {{ $t['tot_faltas'] }} · Tardanzas: {{ $t['tot_tardanzas'] }} ·
                <span class="text-warning font-weight-bold">Descuento total: S/ {{ number_format($t['tot_descuento'], 2) }}</span>
            </span>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Entrada 1</th>
                    <th>Salida 1</th>
                    <th>Entrada 2</th>
                    <th>Salida 2</th>
                    <th>Tardanza (min)</th>
                    <th class="text-right">Descuento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($t['dias'] as $dia)
                <tr class="{{ in_array($dia['estado'], ['FALTA', 'FALTA_GRAVE']) ? 'table-danger' : ($dia['estado'] == 'TARDANZA' ? 'table-warning' : '') }}">
                    <td>{{ $dia['fecha'] }}</td>
                    <td><span class="badge badge-{{ $dia['estado'] == 'PUNTUAL' ? 'success' : ($dia['estado'] == 'TARDANZA' ? 'warning' : ($dia['estado'] == 'FALTA' ? 'danger' : ($dia['estado'] == 'FALTA_GRAVE' ? 'dark' : 'secondary'))) }}">{{ $dia['estado_label'] }}</span></td>
                    <td>{{ $dia['entrada_1'] }}</td>
                    <td>{{ $dia['salida_1'] }}</td>
                    <td>{{ $dia['entrada_2'] }}</td>
                    <td>{{ $dia['salida_2'] }}</td>
                    <td>{{ $dia['tardanza_min'] }}</td>
                    <td class="text-right font-weight-bold">S/ {{ number_format($dia['descuento'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endif
@endsection
