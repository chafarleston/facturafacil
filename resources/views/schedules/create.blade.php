@extends('layouts.admin')
@section('title', 'Nuevo Horario')
@section('page_title', 'Nuevo Horario')

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Crear Horario</h3></div>
    <form method="POST" action="{{ route('schedules.store') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Ej: Corrido 8-5, Dividido 8-13/14-18">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" id="tipoSelect" class="form-control">
                            <option value="corrido">Corrido (1 entrada)</option>
                            <option value="dividido">Dividido (2 entradas)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <h5>Turno 1</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Entrada 1</label>
                        <input type="time" name="entrada_1" class="form-control" value="{{ old('entrada_1', '08:00') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Salida 1</label>
                        <input type="time" name="salida_1" class="form-control" value="{{ old('salida_1', '17:00') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tolerancia entrada 1 (min)</label>
                        <input type="number" name="tolerancia_1" class="form-control" min="0" max="120" value="{{ old('tolerancia_1', 0) }}">
                    </div>
                </div>
            </div>

            <h5>Turno 2 (solo horario dividido)</h5>
            <div class="row" id="turno2Row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Entrada 2</label>
                        <input type="time" name="entrada_2" class="form-control" value="{{ old('entrada_2', '14:00') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Salida 2</label>
                        <input type="time" name="salida_2" class="form-control" value="{{ old('salida_2', '18:00') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tolerancia entrada 2 (min)</label>
                        <input type="number" name="tolerancia_2" class="form-control" min="0" max="120" value="{{ old('tolerancia_2', 0) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Días laborables</label><br>
                @php $days = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6, 'Domingo' => 7]; @endphp
                @foreach($days as $label => $num)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="dias_laborables[]" value="{{ $num }}" {{ in_array($num, [1,2,3,4,5,6]) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
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
