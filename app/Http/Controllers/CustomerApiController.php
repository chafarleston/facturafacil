<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use App\Services\SunatCustomerStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
                    'sunat_estado' => $customer->sunat_estado,
                    'sunat_condicion' => $customer->sunat_condicion,
                    'sunat_bloqueado' => $customer->isSunatBlocked(),
                ]
            ]);
        }
        
        return response()->json(['found' => false]);
    }

    public function quickStore(Request $request, SunatCustomerStatusService $statusService)
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

        $error = $statusService->customerValidationError(
            $validated['documento_tipo'],
            $validated['documento_numero']
        );

        if ($error) {
            throw ValidationException::withMessages(['documento_numero' => $error]);
        }

        $customer = Customer::create($validated);
        $statusService->syncCustomer($customer);
        
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