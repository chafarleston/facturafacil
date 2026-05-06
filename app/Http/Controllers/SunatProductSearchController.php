<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SunatProduct;

class SunatProductSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query', '');
        $results = SunatProduct::query()
            ->where('codigo', 'like', "%{$query}%")
            ->orWhere('descripcion', 'like', "%{$query}%")
            ->orderBy('descripcion')
            ->take(20)
            ->get(['codigo', 'descripcion']);

        return response()->json($results);
    }
}
