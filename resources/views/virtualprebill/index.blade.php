@extends('layouts.admin')
@section('title', 'Precuenta Virtual')
@section('page_title', 'Precuenta Virtual')

@push('styles')
<style>
    @media print {
        .main-sidebar, .main-header, .content-header, .main-footer, .no-print { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .qr-card { break-inside: avoid; page-break-inside: avoid; }
    }
    .qr-url { font-size: 11px; color: #6c757d; word-break: break-all; }
    .qr-card img { width: 100%; max-width: 220px; height: auto; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">

        <div class="card card-primary no-print">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode"></i> Configuración</h3>
            </div>
            <form method="POST" action="{{ route('virtual-prebill.update') }}">
                @csrf
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="enabled" id="enabled" value="1"
                            {{ $enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="enabled">
                            Activar precuenta virtual (QR por mesa)
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="domain">Dominio público</label>
                        <input type="url" name="domain" id="domain" class="form-control" maxlength="255"
                               placeholder="https://facturafacil.restauranterealcomputer.pro"
                               value="{{ old('domain', $domain) }}" required>
                        <small class="text-muted">
                            Se usa para construir el enlace de cada QR: <b>{dominio}/{slug de la mesa}</b>.
                        </small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        Al activar, cada mesa tendrá un QR que abre su precuenta pública (sin login).
                        Ejemplo: <b>{{ rtrim($domain ?: 'https://tu-dominio.com', '/') }}/mesa01</b>.
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                    <a href="{{ route('restaurant.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        @if($enabled && $domain !== '')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-table"></i> QR de mesas ({{ $tables->count() }})</h3>
                <button type="button" class="btn btn-sm btn-outline-primary no-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir QRs
                </button>
            </div>
            <div class="card-body">
                @if($tables->count())
                <div class="row">
                    @foreach($tables as $item)
                    <div class="col-6 col-md-4 col-lg-3 mb-3 qr-card">
                        <div class="border rounded p-3 text-center h-100">
                            <div class="font-weight-bold mb-1">{{ $item['table']->name }}</div>
                            <div class="text-muted small mb-2">
                                {{ $item['table']->floor->name ?? '' }}
                            </div>
                            <img src="{{ $item['qr'] }}" alt="QR {{ $item['table']->name }}">
                            <div class="qr-url mt-2">{{ $item['url'] }}</div>
                            <div class="mt-2 no-print">
                                <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Abrir
                                </a>
                                <a href="{{ $item['qr'] }}" download="{{ $item['table']->slug }}.png" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download"></i> PNG
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted mb-0">No hay mesas registradas.</p>
                @endif
            </div>
        </div>
        @elseif($enabled && $domain === '')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Ingrese el dominio público para generar los QR.
        </div>
        @else
        <div class="alert alert-secondary">
            <i class="fas fa-info-circle"></i> Active la precuenta virtual para generar los QR de las mesas.
        </div>
        @endif

    </div>
</div>
@endsection
