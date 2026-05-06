<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        $suppliers = Supplier::where('company_id', $companyId)->orderBy('nombre')->get();
        return view('suppliers.index', compact('suppliers', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        return view('suppliers.create', compact('companyId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|max:11',
        ]);

        $companyId = $request->get('company_id', Auth::user()->company_id);
        
        Supplier::create([
            'company_id' => $companyId,
            'nombre' => $request->nombre,
            'ruc' => $request->ruc,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'estado' => $request->estado ?? 'ACT',
        ]);

        return redirect()->route('suppliers.index', ['company_id' => $companyId])
            ->with('success', 'Proveedor creado correctamente');
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|max:11',
        ]);

        $supplier->update([
            'nombre' => $request->nombre,
            'ruc' => $request->ruc,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'estado' => $request->estado ?? $supplier->estado,
        ]);

        return redirect()->route('suppliers.index', ['company_id' => $supplier->company_id])
            ->with('success', 'Proveedor actualizado correctamente');
    }

    public function destroy(Supplier $supplier)
    {
        $companyId = $supplier->company_id;
        $supplier->delete();
        return redirect()->route('suppliers.index', ['company_id' => $companyId])
            ->with('success', 'Proveedor eliminado correctamente');
    }
}