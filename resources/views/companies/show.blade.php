@extends('layouts.admin')
@section('title', 'Ver Empresa')
@section('page_title', 'Ver Empresa')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Empresa: {{ $company->razon_social }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">RUC</span>
                                <span class="info-box-number">{{ $company->ruc }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tipo</span>
                                <span class="info-box-number">{{ $company->getTipoContribuyenteNombre() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-store"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Nombre Comercial</span>
                                <span class="info-box-number">{{ $company->nombre_comercial }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dirección</span>
                                <span class="info-box-number">{{ $company->direccion }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-phone"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Teléfono</span>
                                <span class="info-box-number">{{ $company->telefono }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-{{ $company->estado == 'ACT' ? 'success' : 'danger' }}"><i class="fas fa-power-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Estado</span>
                                <span class="info-box-number">{{ $company->estado }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Certificado Digital SUNAT</h3>
            </div>
            <div class="card-body">
                @if($company->certificado_path)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Certificado cargado: {{ $company->certificado_path }}
                    @if($company->certificado_vence)
                    <br><small>Vence: {{ $company->certificado_vence }}</small>
                    @endif
                </div>
                @endif

                <form method="POST" action="{{ route('companies.certificate', $company) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Archivo Certificate (.pfx)</label>
                                <input type="file" name="certificado" accept=".p12,.pfx" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contraseña del Certificate</label>
                                <input type="password" name="certificado_password" class="form-control" placeholder="Contraseña">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Subir Certificate</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection