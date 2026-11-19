@extends('layouts.admin')
@section('title', 'Nueva preventa')
@section('page_title', 'Preventas / ' . $tipos[$tipo])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Nuevo registro de {{ strtolower($tipos[$tipo]) }}</h3>
            </div>
            <div class="card-body">
                @include('preventas._form', ['preventa' => null, 'tipo' => $tipo, 'tipos' => $tipos, 'estados' => $estados])
            </div>
        </div>
    </div>
</div>
@endsection
