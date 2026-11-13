<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Ubigeo;
use App\Services\SunatCustomerStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $customers = Customer::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->paginate(15);

        return view('customers.index', compact('customers', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id;
        return view('customers.create', compact('companyId'));
    }

    public function store(Request $request, SunatCustomerStatusService $statusService)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'documento_tipo' => 'required|in:1,6',
            'documento_numero' => 'required|max:20',
            'nombre' => 'required',
            'direccion' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email',
            'ubigeo' => 'nullable|size:6',
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

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'customer' => $customer
            ]);
        }

        return redirect()->route('customers.index', ['company_id' => $request->company_id])
            ->with('success', 'Cliente creado correctamente');
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        if (!$customer->canBeEdited()) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'El cliente está bloqueado y no puede editarse mientras su RUC no esté ACTIVO en SUNAT.');
        }

        $companyId = $customer->company_id;
        return view('customers.edit', compact('customer', 'companyId'));
    }

    public function update(Request $request, Customer $customer, SunatCustomerStatusService $statusService)
    {
        if (!$customer->canBeEdited()) {
            return back()->with('error', 'El cliente está bloqueado y no puede editarse mientras su RUC no esté ACTIVO en SUNAT.');
        }

        $validated = $request->validate([
            'documento_tipo' => 'required|in:1,6',
            'documento_numero' => 'required|max:20',
            'nombre' => 'required',
            'direccion' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email',
            'ubigeo' => 'nullable|size:6',
        ]);

        $error = $statusService->customerValidationError(
            $validated['documento_tipo'],
            $validated['documento_numero']
        );

        if ($error) {
            throw ValidationException::withMessages(['documento_numero' => $error]);
        }

        $customer->update($validated);
        $statusService->syncCustomer($customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Cliente actualizado');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['estado' => 'INACTIVO']);
        return back()->with('success', 'Cliente desactivado');
    }
}