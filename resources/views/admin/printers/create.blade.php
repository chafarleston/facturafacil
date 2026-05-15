@extends('layouts.admin')
@section('title', 'Nueva Impresora')
@section('page_title', 'Nueva Impresora')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Registrar Impresora</h3>
    </div>
    <form method="POST" action="{{ route('printers.store') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre identificativo</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: EPSON Cocina 1">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="type" class="form-control" id="printerType" onchange="togglePrinterType()">
                            <option value="local">Local (USB/WiFi)</option>
                            <option value="network">Red (IP)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Asignar a</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">Sin asignar</option>
                            @foreach($assignments as $val => $label)
                                @if($val) <option value="{{ $val }}">{{ $label }}</option> @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="localFields">
                <div class="form-group">
                    <label>Nombre de la impresora en Windows</label>
                    <input type="text" name="printer_name" class="form-control" placeholder="Ej: EPSON TM-T88V">
                    <small class="text-muted">Debe coincidir con el nombre exacto en "Dispositivos e Impresoras" de Windows</small>
                </div>
            </div>

            <div id="networkFields" style="display:none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dirección IP</label>
                            <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.100">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Puerto</label>
                            <input type="number" name="port" class="form-control" value="9100">
                            <small class="text-muted">Generalmente 9100 (RAW) o 631 (IPP)</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" name="active" class="form-check-input" value="1" id="activeCheck" checked>
                <label class="form-check-label" for="activeCheck">Activo</label>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('printers.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function togglePrinterType() {
    const type = document.getElementById('printerType').value;
    document.getElementById('localFields').style.display = type === 'local' ? 'block' : 'none';
    document.getElementById('networkFields').style.display = type === 'network' ? 'block' : 'none';
}
</script>
@endpush
@endsection
