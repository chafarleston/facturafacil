@extends('layouts.admin')
@section('title', $title)
@section('page_title', $title)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ $title }}</h3>
            </div>
            <form action="{{ route('documents.store', $tipo) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Serie</label>
                                <select name="serie_id" class="form-control" required>
                                    @foreach($series as $s)
                                    <option value="{{ $s->id }}">{{ $s->serie }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha de Emisión</label>
                                <input type="date" name="fecha_emision" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Datos del {{ $tipo === 'T' ? 'Destinatario' : 'Proveedor' }}</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Doc.</label>
                                <select name="entity_tipo_doc" class="form-control">
                                    <option value="6">RUC</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Número</label>
                                <input type="text" name="entity_num_doc" class="form-control" maxlength="11" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Razón Social</label>
                                <input type="text" name="entity_razon_social" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="entity_direccion" class="form-control">
                    </div>

                    <hr>
                    <h5>Detalle</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total</label>
                                <input type="number" step="0.01" min="0" name="total" class="form-control" value="0.00" required>
                            </div>
                        </div>
                        @if(in_array($tipo, ['R', 'P']))
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Régimen</label>
                                <select name="regimen" class="form-control">
                                    <option value="01">Ventas Internas</option>
                                    <option value="02">Exportación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tasa (%)</label>
                                <input type="number" step="0.01" name="tasa" class="form-control" value="3">
                            </div>
                        </div>
                        @endif
                        @if($tipo === 'R')
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Imp. Retenido</label>
                                <input type="number" step="0.01" name="imp_retenido" class="form-control" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Imp. Pagado</label>
                                <input type="number" step="0.01" name="imp_pagado" class="form-control" value="0.00">
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($tipo === 'T')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dirección de Partida</label>
                                <input type="text" name="dir_partida" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dirección de Llegada</label>
                                <input type="text" name="dir_llegada" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Observación</label>
                        <input type="text" name="observacion" class="form-control">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('documents.index', $tipo) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
