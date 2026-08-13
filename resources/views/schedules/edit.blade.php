@extends('layouts.admin')
@section('title', 'Editar Horario')
@section('page_title', 'Editar Horario')

@section('content')
<div class="card card-warning">
    <div class="card-header"><h3 class="card-title">Editar: {{ $schedule->name }}</h3></div>
    <form method="POST" action="{{ route('schedules.update', $schedule) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $schedule->name) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" id="tipoSelect" class="form-control">
                            <option value="corrido" {{ $schedule->tipo == 'corrido' ? 'selected' : '' }}>Corrido (1 entrada)</option>
                            <option value="dividido" {{ $schedule->tipo == 'dividido' ? 'selected' : '' }}>Dividido (2 entradas)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="ACTIVO" {{ $schedule->estado == 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                            <option value="INACTIVO" {{ $schedule->estado == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <h5>Turno 1</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Entrada 1</label>
                        <input type="time" name="entrada_1" class="form-control" value="{{ old('entrada_1', substr($schedule->entrada_1 ?? '08:00', 0, 5)) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Salida 1</label>
                        <input type="time" name="salida_1" class="form-control" value="{{ old('salida_1', substr($schedule->salida_1 ?? '17:00', 0, 5)) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tolerancia entrada 1 (min)</label>
                        <input type="number" name="tolerancia_1" class="form-control" min="0" max="120" value="{{ old('tolerancia_1', $schedule->tolerancia_1) }}">
                    </div>
                </div>
            </div>

            <h5>Turno 2 (solo horario dividido)</h5>
            <div class="row" id="turno2Row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Entrada 2</label>
                        <input type="time" name="entrada_2" class="form-control" value="{{ old('entrada_2', substr($schedule->entrada_2 ?? '14:00', 0, 5)) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Salida 2</label>
                        <input type="time" name="salida_2" class="form-control" value="{{ old('salida_2', substr($schedule->salida_2 ?? '18:00', 0, 5)) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tolerancia entrada 2 (min)</label>
                        <input type="number" name="tolerancia_2" class="form-control" min="0" max="120" value="{{ old('tolerancia_2', $schedule->tolerancia_2) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Días laborables</label><br>
                @php $days = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6, 'Domingo' => 7]; $selectedDays = $schedule->dias_laborables ?? []; @endphp
                @foreach($days as $label => $num)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="dias_laborables[]" value="{{ $num }}" {{ in_array($num, $selectedDays) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
    function toggleTurno2() {
        document.getElementById('turno2Row').style.display = document.getElementById('tipoSelect').value === 'dividido' ? '' : 'none';
    }
    document.getElementById('tipoSelect').addEventListener('change', toggleTurno2);
    toggleTurno2();
</script>
@endpush
