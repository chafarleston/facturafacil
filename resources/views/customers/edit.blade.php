@extends('layouts.admin')
@section('title', 'Editar Cliente')
@section('page_title', 'Editar Cliente')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Cliente</h3>
    </div>
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Documento</label>
                        <select name="documento_tipo" class="form-control" required>
                            <option value="1" {{ $customer->documento_tipo == '1' ? 'selected' : '' }}>DNI</option>
                            <option value="6" {{ $customer->documento_tipo == '6' ? 'selected' : '' }}>RUC</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Número Documento</label>
                        <input type="text" name="documento_numero" value="{{ $customer->documento_numero }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre / Razón Social</label>
                <input type="text" name="nombre" value="{{ $customer->nombre }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $customer->direccion }}" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Departamento</label>
                        <select id="departamento" class="form-control" onchange="loadProvincias()">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Provincia</label>
                        <select id="provincia" class="form-control" onchange="loadDistritos()">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Distrito</label>
                        <select id="distrito" class="form-control" onchange="updateUbigeo()">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>
            </div>
            <input type="hidden" name="ubigeo" id="ubigeo_codigo" value="{{ $customer->ubigeo }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ $customer->telefono }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $customer->email }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const companyId = {{ $companyId }};
const currentUbigeo = "{{ $customer->ubigeo }}";

document.addEventListener('DOMContentLoaded', function() {
    loadDepartamentosEdit();
});

function loadDepartamentosEdit() {
    fetch('/ubigeo/departamentos')
        .then(res => res.json())
        .then(data => {
            const deptSelect = document.getElementById('departamento');
            data.forEach(dept => {
                const opt = document.createElement('option');
                opt.value = dept;
                opt.textContent = dept;
                deptSelect.appendChild(opt);
            });
            if (currentUbigeo) {
                loadUbigeoData(currentUbigeo);
            }
        });
}

function loadUbigeoData(codigo) {
    fetch('/ubigeo/by-codigo?codigo=' + codigo)
        .then(res => res.json())
        .then(data => {
            if (data) {
                document.getElementById('departamento').value = data.departamento;
                loadProvinciasEdit(data.departamento, data.provincia, data.distrito);
            }
        });
}

function loadProvinciasEdit(dept, selectedProv, selectedDist) {
    fetch('/ubigeo/provincias?departamento=' + encodeURIComponent(dept))
        .then(res => res.json())
        .then(data => {
            const provSelect = document.getElementById('provincia');
            provSelect.innerHTML = '<option value="">Seleccionar</option>';
            data.forEach(prov => {
                const opt = document.createElement('option');
                opt.value = prov;
                opt.textContent = prov;
                provSelect.appendChild(opt);
            });
            if (selectedProv) {
                provSelect.value = selectedProv;
                loadDistritosEdit(dept, selectedProv, selectedDist);
            }
        });
}

function loadDistritosEdit(dept, prov, selectedDist) {
    fetch('/ubigeo/distritos?departamento=' + encodeURIComponent(dept) + '&provincia=' + encodeURIComponent(prov))
        .then(res => res.json())
        .then(data => {
            const distSelect = document.getElementById('distrito');
            distSelect.innerHTML = '<option value="">Seleccionar</option>';
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.codigo;
                opt.textContent = d.distrito;
                opt.dataset.distrito = d.distrito;
                distSelect.appendChild(opt);
            });
            if (selectedDist) {
                const match = Array.from(distSelect.options).find(o => o.dataset.distrito === selectedDist);
                if (match) {
                    distSelect.value = match.value;
                }
            }
            document.getElementById('ubigeo_codigo').value = currentUbigeo;
        });
}

function loadProvincias() {
    const dept = document.getElementById('departamento').value;
    const provSelect = document.getElementById('provincia');
    const distSelect = document.getElementById('distrito');
    
    provSelect.innerHTML = '<option value="">Seleccionar</option>';
    distSelect.innerHTML = '<option value="">Seleccionar</option>';
    provSelect.disabled = true;
    distSelect.disabled = true;
    document.getElementById('ubigeo_codigo').value = '';
    
    if (!dept) return;
    
    fetch('/ubigeo/provincias?departamento=' + encodeURIComponent(dept))
        .then(res => res.json())
        .then(data => {
            provSelect.disabled = false;
            data.forEach(prov => {
                const opt = document.createElement('option');
                opt.value = prov;
                opt.textContent = prov;
                provSelect.appendChild(opt);
            });
        });
}

function loadDistritos() {
    const dept = document.getElementById('departamento').value;
    const prov = document.getElementById('provincia').value;
    const distSelect = document.getElementById('distrito');
    
    distSelect.innerHTML = '<option value="">Seleccionar</option>';
    distSelect.disabled = true;
    document.getElementById('ubigeo_codigo').value = '';
    
    if (!dept || !prov) return;
    
    fetch('/ubigeo/distritos?departamento=' + encodeURIComponent(dept) + '&provincia=' + encodeURIComponent(prov))
        .then(res => res.json())
        .then(data => {
            distSelect.disabled = false;
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.codigo;
                opt.textContent = d.distrito;
                opt.dataset.distrito = d.distrito;
                distSelect.appendChild(opt);
            });
        });
}

function updateUbigeo() {
    const distSelect = document.getElementById('distrito');
    const selected = distSelect.options[distSelect.selectedIndex];
    if (selected && selected.value) {
        document.getElementById('ubigeo_codigo').value = selected.value;
    }
}
</script>
@endpush