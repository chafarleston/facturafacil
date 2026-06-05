@extends('layouts.admin')
@section('title', 'Editar Empresa')
@section('page_title', 'Editar Empresa')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Empresa</h3>
    </div>
    <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" name="ruc" value="{{ $company->ruc }}" class="form-control" required maxlength="11">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Contribuyente (SUNAT)</label>
                        <select name="tipo_contribuyente" class="form-control">
                            <option value="01" {{ $company->tipo_contribuyente == '01' ? 'selected' : '' }}>01-Persona Natural sin Negocio</option>
                            <option value="02" {{ $company->tipo_contribuyente == '02' ? 'selected' : '' }}>02-Persona Natural con Negocio</option>
                            <option value="03" {{ $company->tipo_contribuyente == '03' ? 'selected' : '' }}>03-Sociedad Conyugal sin Negocio</option>
                            <option value="04" {{ $company->tipo_contribuyente == '04' ? 'selected' : '' }}>04-Sociedad Conyugal con Negocio</option>
                            <option value="05" {{ $company->tipo_contribuyente == '05' ? 'selected' : '' }}>05-Sucesión Indivisa sin Negocio</option>
                            <option value="06" {{ $company->tipo_contribuyente == '06' ? 'selected' : '' }}>06-Sucesión Indivisa con Negocio</option>
                            <option value="07" {{ $company->tipo_contribuyente == '07' ? 'selected' : '' }}>07-Empresa Individual de Resp. Ltda</option>
                            <option value="08" {{ $company->tipo_contribuyente == '08' ? 'selected' : '' }}>08-Sociedad Civil</option>
                            <option value="09" {{ $company->tipo_contribuyente == '09' ? 'selected' : '' }}>09-Sociedad Irregular</option>
                            <option value="10" {{ $company->tipo_contribuyente == '10' ? 'selected' : '' }}>10-Asociación en Participación</option>
                            <option value="11" {{ $company->tipo_contribuyente == '11' ? 'selected' : '' }}>11-Asociación</option>
                            <option value="12" {{ $company->tipo_contribuyente == '12' ? 'selected' : '' }}>12-Fundación</option>
                            <option value="13" {{ $company->tipo_contribuyente == '13' ? 'selected' : '' }}>13-Sociedad en Comandita Simple</option>
                            <option value="14" {{ $company->tipo_contribuyente == '14' ? 'selected' : '' }}>14-Sociedad Colectiva</option>
                            <option value="15" {{ $company->tipo_contribuyente == '15' ? 'selected' : '' }}>15-Instituciones Públicas</option>
                            <option value="16" {{ $company->tipo_contribuyente == '16' ? 'selected' : '' }}>16-Instituciones Religiosas</option>
                            <option value="17" {{ $company->tipo_contribuyente == '17' ? 'selected' : '' }}>17-Sociedad de Beneficencia</option>
                            <option value="18" {{ $company->tipo_contribuyente == '18' ? 'selected' : '' }}>18-Entidades de Auxilio Mutuo</option>
                            <option value="19" {{ $company->tipo_contribuyente == '19' ? 'selected' : '' }}>19-Universidad, Centros Educativos y Culturales</option>
                            <option value="20" {{ $company->tipo_contribuyente == '20' ? 'selected' : '' }}>20-Gobierno Regional/Local</option>
                            <option value="21" {{ $company->tipo_contribuyente == '21' ? 'selected' : '' }}>21-Gobierno Central</option>
                            <option value="22" {{ $company->tipo_contribuyente == '22' ? 'selected' : '' }}>22-Comunidad Laboral</option>
                            <option value="23" {{ $company->tipo_contribuyente == '23' ? 'selected' : '' }}>23-Comunidad Campesina, Nativa, Comunal</option>
                            <option value="24" {{ $company->tipo_contribuyente == '24' ? 'selected' : '' }}>24-Cooperativas, SAIS, CAPS</option>
                            <option value="25" {{ $company->tipo_contribuyente == '25' ? 'selected' : '' }}>25-Empresa de Propiedad Social</option>
                            <option value="26" {{ $company->tipo_contribuyente == '26' ? 'selected' : '' }}>26-Sociedad Anónima</option>
                            <option value="27" {{ $company->tipo_contribuyente == '27' ? 'selected' : '' }}>27-Sociedad en Comandita por Acciones</option>
                            <option value="28" {{ $company->tipo_contribuyente == '28' ? 'selected' : '' }}>28-Sociedad Com.Respons. Ltda</option>
                            <option value="29" {{ $company->tipo_contribuyente == '29' ? 'selected' : '' }}>29-Sucursal Empresa Extranjera</option>
                            <option value="30" {{ $company->tipo_contribuyente == '30' ? 'selected' : '' }}>30-Empresa de Derecho Público</option>
                            <option value="31" {{ $company->tipo_contribuyente == '31' ? 'selected' : '' }}>31-Empresa Estatal de Derecho Privado</option>
                            <option value="32" {{ $company->tipo_contribuyente == '32' ? 'selected' : '' }}>32-Empresa de Economía Mixta</option>
                            <option value="33" {{ $company->tipo_contribuyente == '33' ? 'selected' : '' }}>33-Accionariado del Estado</option>
                            <option value="34" {{ $company->tipo_contribuyente == '34' ? 'selected' : '' }}>34-Misiones Diplomáticas y Org. Internacionales</option>
                            <option value="35" {{ $company->tipo_contribuyente == '35' ? 'selected' : '' }}>35-Junta de Propietarios</option>
                            <option value="36" {{ $company->tipo_contribuyente == '36' ? 'selected' : '' }}>36-Oficina de Representación de No Domiciliado</option>
                            <option value="37" {{ $company->tipo_contribuyente == '37' ? 'selected' : '' }}>37-Fondos Mutuos de Inversión</option>
                            <option value="38" {{ $company->tipo_contribuyente == '38' ? 'selected' : '' }}>38-Sociedad Anónima Abierta</option>
                            <option value="39" {{ $company->tipo_contribuyente == '39' ? 'selected' : '' }}>39-Sociedad Anónima Cerrada</option>
                            <option value="40" {{ $company->tipo_contribuyente == '40' ? 'selected' : '' }}>40-Contratos de Colaboración Empresarial</option>
                            <option value="41" {{ $company->tipo_contribuyente == '41' ? 'selected' : '' }}>41-Entidad Institucional Coop.Técnica - ENIEX</option>
                            <option value="42" {{ $company->tipo_contribuyente == '42' ? 'selected' : '' }}>42-Comunidad de Bienes</option>
                            <option value="43" {{ $company->tipo_contribuyente == '43' ? 'selected' : '' }}>43-Sociedad Minera de Resp. Limitada</option>
                            <option value="44" {{ $company->tipo_contribuyente == '44' ? 'selected' : '' }}>44-Asociación, Fundación y Comité No Inscritos</option>
                            <option value="45" {{ $company->tipo_contribuyente == '45' ? 'selected' : '' }}>45-Partidos, Movimientos, Alianzas Políticas</option>
                            <option value="46" {{ $company->tipo_contribuyente == '46' ? 'selected' : '' }}>46-Asociación de Hecho de Profesionales</option>
                            <option value="47" {{ $company->tipo_contribuyente == '47' ? 'selected' : '' }}>47-CAFAES y SubCAFAES</option>
                            <option value="48" {{ $company->tipo_contribuyente == '48' ? 'selected' : '' }}>48-Sindicatos y Federaciones</option>
                            <option value="49" {{ $company->tipo_contribuyente == '49' ? 'selected' : '' }}>49-Colegios Profesionales</option>
                            <option value="50" {{ $company->tipo_contribuyente == '50' ? 'selected' : '' }}>50-Comités Inscritos</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Razón Social</label>
                <input type="text" name="razon_social" value="{{ $company->razon_social }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Nombre Comercial</label>
                <input type="text" name="nombre_comercial" value="{{ $company->nombre_comercial }}" class="form-control">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $company->direccion }}" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Departamento</label>
                        <input type="text" name="departamento" value="{{ $company->departamento }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Provincia</label>
                        <input type="text" name="provincia" value="{{ $company->provincia }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Distrito</label>
                        <input type="text" name="distrito" value="{{ $company->distrito }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ $company->telefono }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $company->email }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title">Configuración de IGV</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tipo de Impuesto</label>
                        <select name="tax_type" class="form-control" id="taxType">
                            <option value="general" {{ $company->tax_type === 'general' ? 'selected' : '' }}>General (IGV 18%)</option>
                            <option value="restaurant" {{ $company->tax_type === 'restaurant' ? 'selected' : '' }}>Restaurante (IGV 10.5%)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>IGV General (%)</label>
                                <input type="number" name="igv_percent" class="form-control" step="0.01" min="0" max="100" value="{{ $company->igv_percent ?? 18 }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>IGV Reducido Restaurante (%)</label>
                                <input type="number" name="reduced_igv_percent" class="form-control" step="0.01" min="0" max="100" value="{{ $company->reduced_igv_percent ?? 10.50 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title">Configuración SUNAT</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Entorno SUNAT</label>
                        <select name="soap_type_id" class="form-control">
                            <option value="01" {{ $company->soap_type_id == '01' ? 'selected' : '' }}>Beta (Demo / Pruebas)</option>
                            <option value="02" {{ $company->soap_type_id == '02' ? 'selected' : '' }}>Producción</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>SOAP Usuario</label>
                                <input type="text" name="soap_username" class="form-control" value="{{ $company->soap_username }}" placeholder="Ej: 20000000001MODDATOS">
                                <small class="text-muted">Usuario secundario SUNAT (RUC + nombre de usuario)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>SOAP Contraseña</label>
                                <input type="password" name="soap_password" class="form-control" value="{{ $company->soap_password }}" placeholder="Contraseña del usuario secundario">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Certificado Digital SUNAT (.p12 / .pfx)</label>
                        @if($company->certificado_path)
                        <div class="alert alert-success py-2 px-3 mb-2">
                            <i class="fas fa-check-circle"></i> {{ $company->certificado_path }}
                            @if($company->certificado_vence)
                            <br><small>Vence: {{ $company->certificado_vence }}</small>
                            @endif
                        </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" name="certificado" class="custom-file-input" id="certificadoInput" accept=".p12,.pfx">
                            <label class="custom-file-label" for="certificadoInput">Seleccionar archivo</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Contraseña del Certificado</label>
                        <input type="password" name="certificado_password" class="form-control" placeholder="Contraseña del certificado digital">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Logo de la Empresa</label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" name="logo" class="custom-file-input" id="logoInput" accept="image/*">
                        <label class="custom-file-label" for="logoInput">Seleccionar imagen</label>
                    </div>
                </div>
                <small class="form-text text-muted">Formatos: JPEG, PNG, JPG, GIF, SVG. Tamaño máximo: 2MB</small>
                @if($company->logo)
                <div class="mt-2">
                    <p><strong>Logo actual:</strong></p>
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo actual" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                </div>
                @endif
                <div id="logoPreview" class="mt-2" style="display:none;">
                    <p><strong>Vista previa:</strong></p>
                    <img id="logoPreviewImg" src="" alt="Preview" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('logoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreviewImg').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush