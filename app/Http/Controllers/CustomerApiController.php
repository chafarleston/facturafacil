<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function search(Request $request)
    {
        $companyId = $request->company_id;
        $documento = $request->documento;
        
        $customer = Customer::where('company_id', $companyId)
            ->where('documento_numero', $documento)
            ->first();
        
        if ($customer) {
            return response()->json([
                'found' => true,
                'customer' => [
                    'id' => $customer->id,
                    'nombre' => $customer->nombre,
                    'documento_tipo' => $customer->documento_tipo,
                    'documento_numero' => $customer->documento_numero,
                    'direccion' => $customer->direccion,
                    'email' => $customer->email,
                    'telefono' => $customer->telefono,
                ]
            ]);
        }
        
        return response()->json(['found' => false]);
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'documento_tipo' => 'required|in:1,6',
            'documento_numero' => 'required|max:20',
            'nombre' => 'required',
            'direccion' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email',
        ]);

        $customer = Customer::create($validated);
        
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'nombre' => $customer->nombre,
                'documento_tipo' => $customer->documento_tipo,
                'documento_numero' => $customer->documento_numero,
            ]
        ]);
    }
}