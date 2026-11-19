@extends('layouts.admin')
@section('title', 'Editar preventa')
@section('page_title', 'Preventas / ' . $tipos[$tipo])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Editar {{ $preventa->numero }}</h3>
            </div>
            <div class="card-body">
                @include('preventas._form', ['preventa' => $preventa, 'tipo' => $tipo, 'tipos' => $tipos, 'estados' => $estados])
            </div>
        </div>
    </div>
</div>
@endsection
