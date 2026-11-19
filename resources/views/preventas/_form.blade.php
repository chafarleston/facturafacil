@php
    $editando = isset($preventa);
    $estadoActual = old('estado', $preventa->estado ?? (in_array($tipo, ['pedido', 'soporte'], true) ? 'PENDIENTE' : 'BORRADOR'));
    $fechaActual = old('fecha', isset($preventa) ? $preventa->fecha?->format('Y-m-d') : now()->format('Y-m-d'));
    $vencimientoActual = old('fecha_vencimiento', isset($preventa) ? $preventa->fecha_vencimiento?->format('Y-m-d') : '');
@endphp

<form method="POST"
      action="{{ $editando ? route('preventas.update', $preventa) : route('preventas.store') }}">
    @csrf
    @if($editando)
        @method('PUT')
    @else
        <input type="hidden" name="tipo" value="{{ $tipo }}">
    @endif

    <div class="row">
        <div class="col-md-3 form-group">
            <label>Tipo</label>
            <input type="text" class="form-control" value="{{ $tipos[$tipo] }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label>Número</label>
            <input type="text" class="form-control" value="{{ $preventa->numero ?? 'Se generará al guardar' }}" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label for="estado">Estado <span class="text-danger">*</span></label>
            <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach($estados as $value => $label)
                    <option value="{{ $value }}" {{ $estadoActual === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="monto">Monto (S/)</label>
            <input type="number" step="0.01" min="0" name="monto" id="monto"
                   class="form-control @error('monto') is-invalid @enderror"
                   value="{{ old('monto', $preventa->monto ?? '') }}">
            @error('monto')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="cliente_nombre">Cliente <span class="text-danger">*</span></label>
            <input type="text" name="cliente_nombre" id="cliente_nombre"
                   class="form-control @error('cliente_nombre') is-invalid @enderror"
                   value="{{ old('cliente_nombre', $preventa->cliente_nombre ?? '') }}" required>
            @error('cliente_nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-2 form-group">
            <label for="cliente_documento">DNI / RUC</label>
            <input type="text" name="cliente_documento" id="cliente_documento" maxlength="20"
                   class="form-control @error('cliente_documento') is-invalid @enderror"
                   value="{{ old('cliente_documento', $preventa->cliente_documento ?? '') }}">
            @error('cliente_documento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="cliente_email">Correo</label>
            <input type="email" name="cliente_email" id="cliente_email"
                   class="form-control @error('cliente_email') is-invalid @enderror"
                   value="{{ old('cliente_email', $preventa->cliente_email ?? '') }}">
            @error('cliente_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="cliente_telefono">Teléfono</label>
            <input type="text" name="cliente_telefono" id="cliente_telefono"
                   class="form-control @error('cliente_telefono') is-invalid @enderror"
                   value="{{ old('cliente_telefono', $preventa->cliente_telefono ?? '') }}">
            @error('cliente_telefono')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-group">
        <label for="titulo">Título / asunto <span class="text-danger">*</span></label>
        <input type="text" name="titulo" id="titulo"
               class="form-control @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $preventa->titulo ?? '') }}" required>
        @error('titulo')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="descripcion">Descripción / detalle</label>
            <textarea name="descripcion" id="descripcion" rows="5"
                      class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $preventa->descripcion ?? '') }}</textarea>
            @error('descripcion')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-6 form-group">
            <label for="observaciones">Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="5"
                      class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $preventa->observaciones ?? '') }}</textarea>
            @error('observaciones')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <label for="fecha">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" id="fecha"
                   class="form-control @error('fecha') is-invalid @enderror"
                   value="{{ $fechaActual }}" required>
            @error('fecha')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="col-md-3 form-group">
            <label for="fecha_vencimiento">Fecha de vencimiento</label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
                   class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                   value="{{ $vencimientoActual }}">
            @error('fecha_vencimiento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('preventas.index', ['tipo' => $tipo]) }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
