@extends('layouts.admin')
@section('title', 'Editar Impresora')
@section('page_title', 'Editar Impresora')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar Impresora: {{ $printer->name }}</h3>
    </div>
    <form method="POST" action="{{ route('printers.update', $printer) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre identificativo</label>
                        <input type="text" name="name" class="form-control" value="{{ $printer->name }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="type" class="form-control" id="printerType" onchange="togglePrinterType()">
                            <option value="local" {{ $printer->type == 'local' ? 'selected' : '' }}>Local (USB/WiFi)</option>
                            <option value="network" {{ $printer->type == 'network' ? 'selected' : '' }}>Red (IP)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Asignar a</label>
                        <select name="assigned_to" class="form-control">
                            <option value="">Sin asignar</option>
                            @foreach($assignments as $val => $label)
                                @if($val) <option value="{{ $val }}" {{ $printer->assigned_to == $val ? 'selected' : '' }}>{{ $label }}</option> @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="localFields" style="{{ $printer->type == 'network' ? 'display:none' : '' }}">
                <div class="form-group">
                    <label>Nombre de la impresora en Windows</label>
                    <input type="text" name="printer_name" class="form-control" value="{{ $printer->printer_name }}">
                    <small class="text-muted">Debe coincidir con el nombre exacto en "Dispositivos e Impresoras" de Windows</small>
                </div>
            </div>

            <div id="networkFields" style="{{ $printer->type == 'local' ? 'display:none' : '' }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dirección IP</label>
                            <input type="text" name="ip_address" class="form-control" value="{{ $printer->ip_address }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Puerto</label>
                            <input type="number" name="port" class="form-control" value="{{ $printer->port ?? 9100 }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" name="active" class="form-check-input" value="1" id="activeCheck" {{ $printer->active ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Activo</label>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('printers.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
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
