@extends('layouts.admin')
@section('title', 'Padrón SUNAT')
@section('page_title', 'Padrón SUNAT')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database"></i> Padrón Reducido de RUC</h3>
            </div>
            <div class="card-body">
                <p>El padrón reducido de RUC es un archivo de SUNAT que contiene información de todos los contribuyentes. Permite consultar datos de RUC sin hacer llamadas a la API de SUNAT.</p>

                @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
                @endif

                @if($padronInfo['exists'])
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Padrón descargado</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">Archivo</th>
                                <td>{{ $padronInfo['file'] }}</td>
                            </tr>
                            <tr>
                                <th>Tamaño</th>
                                <td>{{ $padronInfo['size'] }}</td>
                            </tr>
                            <tr>
                                <th>Registros</th>
                                <td>{{ $padronInfo['records'] }}</td>
                            </tr>
                            <tr>
                                <th>Última actualización</th>
                                <td>{{ $padronInfo['last_modified'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Padrón no descargado</strong>
                    <p class="mb-0 mt-2">Para buscar RUCs directamente desde SUNAT, debe descargar el padrón.</p>
                </div>
                @endif

                <form method="POST" action="{{ route('sunat-padron.download') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('¿Descargar el padrón de SUNAT? Esto puede tardar varios minutos.')">
                        <i class="fas fa-download"></i> {{ $padronInfo['exists'] ? 'Actualizar Padrón' : 'Descargar Padrón' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
            </div>
            <div class="card-body">
                <h6>¿Qué es el padrón reducido?</h6>
                <p>Es un archivo que SUNAT publica con información básica de todos los contribuyentes con RUC activo. Incluye:</p>
                <ul>
                    <li>RUC</li>
                    <li>Razón Social / Nombre</li>
                    <li>Estado (Activo, Suspensión, etc.)</li>
                    <li>Condición (Habido, No Habido)</li>
                    <li>Dirección</li>
                    <li>Ubigeo</li>
                </ul>

                <h6>¿Cuándo se actualiza?</h6>
                <p>SUNAT actualiza el padrón semanalmente. Se recomienda descargarlo al menos una vez por semana.</p>

                <h6>Comando manual</h6>
                <div class="bg-light p-3 rounded">
                    <code>php artisan sunat:download-padron</code>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Estado del Sistema</h3>
            </div>
            <div class="card-body">
                @if($padronInfo['exists'])
                <div class="text-center mb-3">
                    <i class="fas fa-database text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 text-success">Padrón Disponible</h5>
                </div>
                <p><strong>Registros:</strong> {{ $padronInfo['records'] }}</p>
                <p><strong>Actualizado:</strong> {{ $padronInfo['last_modified'] }}</p>
                @else
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 text-warning">Padrón No Disponible</h5>
                </div>
                <p class="text-muted">Las búsquedas de RUC se realizarán mediante APIs externas (más lento).</p>
                @endif
            </div>
        </div>

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Buscar RUC</h3>
            </div>
            <div class="card-body">
                <p>Una vez descargado el padrón, podrá buscar contribuyentes directamente desde el sistema.</p>
                <a href="{{ route('customers.create') }}" class="btn btn-outline-primary btn-sm btn-block">
                    <i class="fas fa-user-plus"></i> Crear Cliente
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
