@extends('layouts.admin')
@section('title', 'Reglas de Tardanza y Faltas')
@section('page_title', 'Reglas de Tardanza y Faltas')

@section('content')
<form method="POST" action="{{ route('attendance-rules.update') }}">
    @csrf
    <div class="card">
        <div class="card-header"><h3 class="card-title">Reglas Generales</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Umbral de falta por tardanza (min)</label>
                        <input type="number" name="falta_threshold_min" class="form-control" min="1" max="600" value="{{ $setting->falta_threshold_min }}" required>
                        <small class="text-muted">Si la tardanza supera este límite se considera FALTA. Si no marca entrada en día laborable también es FALTA.</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Umbral de FALTA GRAVE (min)</label>
                        <input type="number" name="falta_grave_threshold_min" class="form-control" min="1" max="600" value="{{ $setting->falta_grave_threshold_min }}" required>
                        <small class="text-muted">Si la tardanza supera este límite (ej. 45 min o 2 horas) se considera FALTA GRAVE y cuenta para la suspensión.</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Días (faltas graves) para SUSPENSIÓN</label>
                        <input type="number" name="suspension_graves_count" class="form-control" min="1" max="30" value="{{ $setting->suspension_graves_count }}" required>
                        <small class="text-muted">Tras este número de faltas graves consecutivas, el trabajador queda suspendido y no podrá marcar entrada.</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Descuento por FALTA / FALTA GRAVE - Tipo</label>
                        <select name="descuento_falta_tipo" class="form-control">
                            <option value="FIJO" {{ $setting->descuento_falta_tipo == 'FIJO' ? 'selected' : '' }}>Monto fijo (S/)</option>
                            <option value="PORCENTAJE" {{ $setting->descuento_falta_tipo == 'PORCENTAJE' ? 'selected' : '' }}>% del sueldo diario</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Descuento por FALTA - Valor</label>
                        <input type="number" name="descuento_falta_valor" class="form-control" step="0.01" min="0" value="{{ $setting->descuento_falta_valor }}" required>
                    </div>
                </div>
            </div>

            <h5>Verificación facial (webcam)</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Modo de marcación</label>
                        <select name="modo_marcacion" class="form-control">
                            <option value="dni" {{ $setting->modo_marcacion == 'dni' ? 'selected' : '' }}>Solo DNI (sin cámara)</option>
                            <option value="webcam" {{ $setting->modo_marcacion == 'webcam' ? 'selected' : '' }}>Solo Webcam (reconoce el rostro)</option>
                            <option value="dni_webcam" {{ $setting->modo_marcacion == 'dni_webcam' ? 'selected' : '' }}>DNI + Webcam (verifica que el rostro coincida)</option>
                        </select>
                        <small class="text-muted">Cómo deben marcar los trabajadores: solo DNI, solo cámara (identifica por rostro), o DNI + cámara (verificación facial).</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Umbral de similitud (0.0 - 1.0)</label>
                        <input type="number" name="reconocimiento_umbral" class="form-control" step="0.01" min="0.1" max="1" value="{{ $setting->reconocimiento_umbral }}" required>
                        <small class="text-muted">Valor más bajo = más estricto. 0.6 es el estándar; ajústelo si hay muchos rechazos o falsos positivos.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tiempo de éxito en pantalla (segundos)</label>
                        <input type="number" name="exito_segundos" class="form-control" min="5" max="120" value="{{ $setting->exito_segundos }}" required>
                        <small class="text-muted">Cuántos segundos se muestra la confirmación de asistencia antes de volver al botón de inicio.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Descuentos por Tardanza</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Minutos de tardanza</th>
                        <th>Tipo</th>
                        <th>Valor (S/ o %)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rules as $rule)
                    <tr>
                        <td><strong>{{ $rule->tardanza_min }} min</strong></td>
                        <td>
                            <select name="rules[{{ $rule->id }}][tipo]" class="form-control">
                                <option value="FIJO" {{ $rule->tipo == 'FIJO' ? 'selected' : '' }}>Monto fijo (S/)</option>
                                <option value="PORCENTAJE" {{ $rule->tipo == 'PORCENTAJE' ? 'selected' : '' }}>% del sueldo diario</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="rules[{{ $rule->id }}][valor]" class="form-control" step="0.01" min="0" value="{{ $rule->valor }}" required>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <small class="text-muted p-2 d-block">Se aplica el descuento del tramo más cercano inferior a los minutos de tardanza (ej. 12 min → tramo 10).</small>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Reglas</button>
</form>
@endsection
