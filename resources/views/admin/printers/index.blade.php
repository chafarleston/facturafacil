@extends('layouts.admin')
@section('title', 'Impresoras')
@section('page_title', 'Asignación de Impresoras')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(!$serverRunning)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Servidor de impresión no disponible. Ejecuta <code>php C:\laragon\www\print-server\print-server.php</code>
        </div>
        @else
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Servidor de impresión conectado.
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Puntos de Impresión</h3>
                <div class="card-tools">
                    <button class="btn btn-info btn-sm" onclick="document.getElementById('detectModal').style.display='flex'">
                        <i class="fas fa-search"></i> Detectar y asignar impresoras
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Punto</th>
                            <th>Impresora</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slots as $slot)
                        <tr>
                            <td><strong>{{ $slot->name }}</strong></td>
                            <td>{{ $slot->printer_name ?? ($slot->ip_address ? $slot->ip_address.':'.$slot->port : '—') }}</td>
                            <td><span class="badge badge-{{ $slot->type === 'local' ? 'info' : 'warning' }}">{{ $slot->type === 'local' ? 'Local' : 'Red' }}</span></td>
                            <td><span class="badge badge-{{ $slot->active ? 'success' : 'secondary' }}">{{ $slot->active ? 'Activo' : 'Inactivo' }}</span></td>
                            <td>
                                <button class="btn btn-xs btn-primary" onclick="document.getElementById('editSlot{{ $slot->id }}').style.display='flex'">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($serverRunning && count($availablePrinters) > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Impresoras detectadas en este equipo</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($availablePrinters as $p)
                    <div class="col-md-4 col-sm-6 mb-2">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="fas fa-print fa-2x mr-3 text-info"></i>
                            <strong>{{ $p['name'] }}</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal detect --}}
<div class="qty-overlay" id="detectModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:25px; border-radius:10px; min-width:450px; max-width:90%;">
        <h5><i class="fas fa-search"></i> Asignar impresora</h5>
        <form method="POST" action="{{ route('printers.detect.post') }}">
            @csrf
            <div class="form-group">
                <label>Punto de impresión</label>
                <select name="slot_id" class="form-control" required>
                    @foreach($slots as $slot)
                    <option value="{{ $slot->id }}">{{ $slot->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Impresora detectada</label>
                <select name="printer_name" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    @foreach($availablePrinters as $p)
                    <option value="{{ $p['name'] }}">{{ $p['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('detectModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Asignar</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit modals --}}
@foreach($slots as $slot)
<div class="qty-overlay" id="editSlot{{ $slot->id }}" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
    <div class="qty-popup" style="background:white; padding:25px; border-radius:10px; min-width:400px; max-width:90%;">
        <h5><i class="fas fa-edit"></i> {{ $slot->name }}</h5>
        <form method="POST" action="{{ route('printers.update', $slot) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nombre en Windows</label>
                <input type="text" name="printer_name" class="form-control" value="{{ $slot->printer_name }}" placeholder="Ej: EPSON TM-T20III">
            </div>
            <div class="form-group">
                <label>O impresora de red</label>
                <div class="row">
                    <div class="col-8"><input type="text" name="ip_address" class="form-control" placeholder="IP" value="{{ $slot->ip_address }}"></div>
                    <div class="col-4"><input type="number" name="port" class="form-control" placeholder="Puerto" value="{{ $slot->port }}"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="type" class="form-control">
                            <option value="local" {{ $slot->type == 'local' ? 'selected' : '' }}>Local</option>
                            <option value="network" {{ $slot->type == 'network' ? 'selected' : '' }}>Red</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Activo</label>
                        <select name="active" class="form-control">
                            <option value="1" {{ $slot->active ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ !$slot->active ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editSlot{{ $slot->id }}').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<style>
.qty-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center; }
.qty-popup { background:white; padding:20px; border-radius:10px; min-width:300px; max-width:90%; }
</style>
@endsection
