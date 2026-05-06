<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;

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

    public function store(Request $request)
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

        Customer::create($validated);

        return redirect()->route('customers.index', ['company_id' => $request->company_id])
            ->with('success', 'Cliente creado correctamente');
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'documento_tipo' => 'required|in:1,6',
            'documento_numero' => 'required|max:20',
            'nombre' => 'required',
            'direccion' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email',
            'ubigeo' => 'nullable|size:6',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)->with('success', 'Cliente actualizado');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['estado' => 'INACTIVO']);
        return back()->with('success', 'Cliente desactivado');
    }
}