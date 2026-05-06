@extends('layouts.admin')
@section('title', 'Nueva Empresa')
@section('page_title', 'Nueva Empresa')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva Empresa</h3>
    </div>
    <form method="POST" action="{{ route('companies.store') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>RUC</label>
                        <input type="text" name="ruc" class="form-control" required maxlength="11">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tipo Contribuyente (SUNAT)</label>
                        <select name="tipo_contribuyente" class="form-control">
                            <option value="01">01-Persona Natural sin Negocio</option>
                            <option value="02">02-Persona Natural con Negocio</option>
                            <option value="03">03-Sociedad Conyugal sin Negocio</option>
                            <option value="04">04-Sociedad Conyugal con Negocio</option>
                            <option value="05">05-Sucesión Indivisa sin Negocio</option>
                            <option value="06">06-Sucesión Indivisa con Negocio</option>
                            <option value="07">07-Empresa Individual de Resp. Ltda</option>
                            <option value="08">08-Sociedad Civil</option>
                            <option value="09">09-Sociedad Irregular</option>
                            <option value="10">10-Asociación en Participación</option>
                            <option value="11">11-Asociación</option>
                            <option value="12">12-Fundación</option>
                            <option value="13">13-Sociedad en Comandita Simple</option>
                            <option value="14">14-Sociedad Colectiva</option>
                            <option value="15">15-Instituciones Públicas</option>
                            <option value="16">16-Instituciones Religiosas</option>
                            <option value="17">17-Sociedad de Beneficencia</option>
                            <option value="18">18-Entidades de Auxilio Mutuo</option>
                            <option value="19">19-Universidad, Centros Educativos y Culturales</option>
                            <option value="20">20-Gobierno Regional/Local</option>
                            <option value="21">21-Gobierno Central</option>
                            <option value="22">22-Comunidad Laboral</option>
                            <option value="23">23-Comunidad Campesina, Nativa, Comunal</option>
                            <option value="24">24-Cooperativas, SAIS, CAPS</option>
                            <option value="25">25-Empresa de Propiedad Social</option>
                            <option value="26">26-Sociedad Anónima</option>
                            <option value="27">27-Sociedad en Comandita por Acciones</option>
                            <option value="28">28-Sociedad Com.Respons. Ltda</option>
                            <option value="29">29-Sucursal Empresa Extranjera</option>
                            <option value="30">30-Empresa de Derecho Público</option>
                            <option value="31">31-Empresa Estatal de Derecho Privado</option>
                            <option value="32">32-Empresa de Economía Mixta</option>
                            <option value="33">33-Accionariado del Estado</option>
                            <option value="34">34-Misiones Diplomáticas y Org. Internacionales</option>
                            <option value="35">35-Junta de Propietarios</option>
                            <option value="36">36-Oficina de Representación de No Domiciliado</option>
                            <option value="37">37-Fondos Mutuos de Inversión</option>
                            <option value="38">38-Sociedad Anónima Abierta</option>
                            <option value="39">39-Sociedad Anónima Cerrada</option>
                            <option value="40">40-Contratos de Colaboración Empresarial</option>
                            <option value="41">41-Entidad Institucional Coop.Técnica - ENIEX</option>
                            <option value="42">42-Comunidad de Bienes</option>
                            <option value="43">43-Sociedad Minera de Resp. Limitada</option>
                            <option value="44">44-Asociación, Fundación y Comité No Inscritos</option>
                            <option value="45">45-Partidos, Movimientos, Alianzas Políticas</option>
                            <option value="46">46-Asociación de Hecho de Profesionales</option>
                            <option value="47">47-CAFAES y SubCAFAES</option>
                            <option value="48">48-Sindicatos y Federaciones</option>
                            <option value="49">49-Colegios Profesionales</option>
                            <option value="50">50-Comités Inscritos</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Razón Social</label>
                <input type="text" name="razon_social" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection