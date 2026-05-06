@extends('layouts.admin')
@section('title', 'Editar Empresa')
@section('page_title', 'Editar Empresa')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Editar Empresa</h3>
    </div>
    <form method="POST" action="{{ route('companies.update', $company) }}">
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
                            <option value="">-- Seleccionar --</option>
                            <option value="01" {{ (string)$company->tipo_contribuyente == '01' ? 'selected' : '' }}>01-Persona Natural sin Negocio</option>
                            <option value="02" {{ (string)$company->tipo_contribuyente == '02' ? 'selected' : '' }}>02-Persona Natural con Negocio</option>
                            <option value="03" {{ (string)$company->tipo_contribuyente == '03' ? 'selected' : '' }}>03-Sociedad Conyugal sin Negocio</option>
                            <option value="04" {{ (string)$company->tipo_contribuyente == '04' ? 'selected' : '' }}>04-Sociedad Conyugal con Negocio</option>
                            <option value="05" {{ (string)$company->tipo_contribuyente == '05' ? 'selected' : '' }}>05-Sucesión Indivisa sin Negocio</option>
                            <option value="06" {{ (string)$company->tipo_contribuyente == '06' ? 'selected' : '' }}>06-Sucesión Indivisa con Negocio</option>
                            <option value="07" {{ (string)$company->tipo_contribuyente == '07' ? 'selected' : '' }}>07-Empresa Individual de Resp. Ltda</option>
                            <option value="08" {{ (string)$company->tipo_contribuyente == '08' ? 'selected' : '' }}>08-Sociedad Civil</option>
                            <option value="09" {{ (string)$company->tipo_contribuyente == '09' ? 'selected' : '' }}>09-Sociedad Irregular</option>
                            <option value="10" {{ (string)$company->tipo_contribuyente == '10' ? 'selected' : '' }}>10-Asociación en Participación</option>
                            <option value="11" {{ (string)$company->tipo_contribuyente == '11' ? 'selected' : '' }}>11-Asociación</option>
                            <option value="12" {{ (string)$company->tipo_contribuyente == '12' ? 'selected' : '' }}>12-Fundación</option>
                            <option value="13" {{ (string)$company->tipo_contribuyente == '13' ? 'selected' : '' }}>13-Sociedad en Comandita Simple</option>
                            <option value="14" {{ (string)$company->tipo_contribuyente == '14' ? 'selected' : '' }}>14-Sociedad Colectiva</option>
                            <option value="15" {{ (string)$company->tipo_contribuyente == '15' ? 'selected' : '' }}>15-Instituciones Públicas</option>
                            <option value="16" {{ (string)$company->tipo_contribuyente == '16' ? 'selected' : '' }}>16-Instituciones Religiosas</option>
                            <option value="17" {{ (string)$company->tipo_contribuyente == '17' ? 'selected' : '' }}>17-Sociedad de Beneficencia</option>
                            <option value="18" {{ (string)$company->tipo_contribuyente == '18' ? 'selected' : '' }}>18-Entidades de Auxilio Mutuo</option>
                            <option value="19" {{ (string)$company->tipo_contribuyente == '19' ? 'selected' : '' }}>19-Universidad, Centros Educativos</option>
                            <option value="20" {{ (string)$company->tipo_contribuyente == '20' ? 'selected' : '' }}>20-Gobierno Regional/Local</option>
                            <option value="21" {{ (string)$company->tipo_contribuyente == '21' ? 'selected' : '' }}>21-Gobierno Central</option>
                            <option value="22" {{ (string)$company->tipo_contribuyente == '22' ? 'selected' : '' }}>22-Comunidad Laboral</option>
                            <option value="23" {{ (string)$company->tipo_contribuyente == '23' ? 'selected' : '' }}>23-Comunidad Campesina</option>
                            <option value="24" {{ (string)$company->tipo_contribuyente == '24' ? 'selected' : '' }}>24-Cooperativas</option>
                            <option value="25" {{ (string)$company->tipo_contribuyente == '25' ? 'selected' : '' }}>25-Empresa de Propiedad Social</option>
                            <option value="26" {{ (string)$company->tipo_contribuyente == '26' ? 'selected' : '' }}>26-Sociedad Anónima</option>
                            <option value="27" {{ (string)$company->tipo_contribuyente == '27' ? 'selected' : '' }}>27-Sociedad en Comandita por Acciones</option>
                            <option value="28" {{ (string)$company->tipo_contribuyente == '28' ? 'selected' : '' }}>28-Sociedad Com.Resp. Ltda</option>
                            <option value="29" {{ (string)$company->tipo_contribuyente == '29' ? 'selected' : '' }}>29-Sucursal Empresa Extranjera</option>
                            <option value="30" {{ (string)$company->tipo_contribuyente == '30' ? 'selected' : '' }}>30-Empresa de Derecho Público</option>
                            <option value="31" {{ (string)$company->tipo_contribuyente == '31' ? 'selected' : '' }}>31-Empresa Estatal de Derecho Privado</option>
                            <option value="32" {{ (string)$company->tipo_contribuyente == '32' ? 'selected' : '' }}>32-Empresa de Economía Mixta</option>
                            <option value="33" {{ (string)$company->tipo_contribuyente == '33' ? 'selected' : '' }}>33-Accionariado del Estado</option>
                            <option value="34" {{ (string)$company->tipo_contribuyente == '34' ? 'selected' : '' }}>34-Misiones Diplomáticas</option>
                            <option value="35" {{ (string)$company->tipo_contribuyente == '35' ? 'selected' : '' }}>35-Junta de Propietarios</option>
                            <option value="36" {{ (string)$company->tipo_contribuyente == '36' ? 'selected' : '' }}>36-Oficina de Representación</option>
                            <option value="37" {{ (string)$company->tipo_contribuyente == '37' ? 'selected' : '' }}>37-Fondos Mutuos de Inversión</option>
                            <option value="38" {{ (string)$company->tipo_contribuyente == '38' ? 'selected' : '' }}>38-Sociedad Anónima Abierta</option>
                            <option value="39" {{ (string)$company->tipo_contribuyente == '39' ? 'selected' : '' }}>39-Sociedad Anónima Cerrada</option>
                            <option value="40" {{ (string)$company->tipo_contribuyente == '40' ? 'selected' : '' }}>40-Contratos de Colaboración</option>
                            <option value="41" {{ (string)$company->tipo_contribuyente == '41' ? 'selected' : '' }}>41-Entidad Coop.Técnica</option>
                            <option value="42" {{ (string)$company->tipo_contribuyente == '42' ? 'selected' : '' }}>42-Comunidad de Bienes</option>
                            <option value="43" {{ (string)$company->tipo_contribuyente == '43' ? 'selected' : '' }}>43-Sociedad Minera de Resp. Ltda</option>
                            <option value="44" {{ (string)$company->tipo_contribuyente == '44' ? 'selected' : '' }}>44-Asociación No Inscritos</option>
                            <option value="45" {{ (string)$company->tipo_contribuyente == '45' ? 'selected' : '' }}>45-Partidos Políticos</option>
                            <option value="46" {{ (string)$company->tipo_contribuyente == '46' ? 'selected' : '' }}>46-Asociación de Hecho</option>
                            <option value="47" {{ (string)$company->tipo_contribuyente == '47' ? 'selected' : '' }}>47-CAFAES</option>
                            <option value="48" {{ (string)$company->tipo_contribuyente == '48' ? 'selected' : '' }}>48-Sindicatos</option>
                            <option value="49" {{ (string)$company->tipo_contribuyente == '49' ? 'selected' : '' }}>49-Colegios Profesionales</option>
                            <option value="50" {{ (string)$company->tipo_contribuyente == '50' ? 'selected' : '' }}>50-Comités Inscritos</option>
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
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection