<?php

namespace App\Http\Controllers;

use App\Models\Ubigeo;
use Illuminate\Http\Request;

class UbigeoController extends Controller
{
    public function getDepartamentos()
    {
        return response()->json(Ubigeo::getDepartamentos());
    }

    public function getProvincias(Request $request)
    {
        $departamento = $request->input('departamento');
        return response()->json(Ubigeo::getProvincias($departamento));
    }

    public function getDistritos(Request $request)
    {
        $departamento = $request->input('departamento');
        $provincia = $request->input('provincia');
        return response()->json(Ubigeo::getDistritos($departamento, $provincia));
    }

    public function getByUbigeo(Request $request)
    {
        $codigo = $request->input('codigo');
        $ubigeo = Ubigeo::where('codigo', $codigo)->first();
        
        if ($ubigeo) {
            return response()->json([
                'codigo' => $ubigeo->codigo,
                'departamento' => $ubigeo->departamento,
                'provincia' => $ubigeo->provincia,
                'distrito' => $ubigeo->distrito
            ]);
        }
        
        return response()->json(null);
    }
}