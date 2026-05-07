<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DecolectaController;
use App\Http\Controllers\UbigeoController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas públicas para búsqueda - sin middleware de auth
Route::get('/decolecta/search', [DecolectaController::class, 'search']);
Route::get('/ubigeo/departamentos', [UbigeoController::class, 'getDepartamentos']);
Route::get('/ubigeo/provincias', [UbigeoController::class, 'getProvincias']);
Route::get('/ubigeo/distritos', [UbigeoController::class, 'getDistritos']);
Route::get('/ubigeo/by-codigo', [UbigeoController::class, 'getByUbigeo']);
