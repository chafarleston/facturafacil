@extends('layouts.admin')
@section('title', 'Nuevo Trabajador')
@section('page_title', 'Nuevo Trabajador')

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Registrar Trabajador</h3></div>
    <form method="POST" action="{{ route('personal.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>DNI <span class="text-danger">*</span></label>
                        <input type="text" name="dni" class="form-control" maxlength="8" value="{{ old('dni') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nombres <span class="text-danger">*</span></label>
                        <input type="text" name="nombres" class="form-control" value="{{ old('nombres') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Apellidos <span class="text-danger">*</span></label>
                        <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Cargo</label>
                        <input type="text" name="cargo" class="form-control" value="{{ old('cargo') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sueldo (S/)</label>
                        <input type="number" name="sueldo" class="form-control" step="0.01" min="0" value="{{ old('sueldo', 0) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Horario</label>
                        <select name="schedule_id" class="form-control">
                            <option value="">Sin horario</option>
                            @foreach($schedules as $s)
                                <option value="{{ $s->id }}" {{ old('schedule_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <h5>Foto y rostro (para verificación facial)</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Foto del trabajador</label>
                        <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*">
                        <small class="text-muted">Suba una foto frontal, con buena luz y sin cubrebocas.</small>
                        <input type="hidden" name="face_descriptor" id="face_descriptor" value="">
                        <div id="faceStatus" class="mt-2"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <img id="fotoPreview" src="#" alt="Vista previa" style="max-height:180px; display:none; border-radius:8px;">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('personal.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    let faceModelsReady = false;
    let faceModelError = false;
    const MODEL_URL = '{{ asset('models/face-api') }}';

    async function loadFaceModels() {
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            faceModelsReady = true;
        } catch (e) {
            faceModelError = true;
            throw e;
        }
    }
    loadFaceModels().catch(e => { document.getElementById('faceStatus').innerHTML = '<span class="text-danger">No se pudieron cargar los modelos faciales.</span>'; });

    function waitForModels(timeoutMs) {
        return new Promise(resolve => {
            if (faceModelsReady || faceModelError) return resolve(faceModelsReady);
            const start = Date.now();
            const iv = setInterval(() => {
                if (faceModelsReady || faceModelError || Date.now() - start > timeoutMs) {
                    clearInterval(iv);
                    resolve(faceModelsReady);
                }
            }, 250);
        });
    }

    document.getElementById('fotoInput').addEventListener('change', async function (e) {
        const file = e.target.files[0];
        const status = document.getElementById('faceStatus');
        const preview = document.getElementById('fotoPreview');
        if (!file) return;
        if (preview) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }

        const ok = await waitForModels(15000);
        if (!ok) {
            status.innerHTML = '<span class="text-warning">Modelos faciales no cargaron. Puede guardar sin rostro.</span>';
            return;
        }

        status.innerHTML = '<span class="text-muted">Procesando rostro...</span>';
        try {
            const img = await new Promise((resolve, reject) => {
                const image = new Image();
                image.onload = () => resolve(image);
                image.onerror = reject;
                image.src = URL.createObjectURL(file);
            });
            const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
            if (detection) {
                const descriptor = Array.from(detection.descriptor);
                document.getElementById('face_descriptor').value = JSON.stringify(descriptor);
                status.innerHTML = '<span class="text-success">Rostro detectado correctamente (descriptor calculado).</span>';
            } else {
                document.getElementById('face_descriptor').value = '';
                status.innerHTML = '<span class="text-danger">No se detectó un rostro en la foto. Puede guardar igualmente, pero sin verificación facial.</span>';
            }
        } catch (err) {
            document.getElementById('face_descriptor').value = '';
            status.innerHTML = '<span class="text-danger">No se pudo procesar la foto. Puede guardar igualmente, pero sin verificación facial.</span>';
        }
    });
</script>
@endpush
