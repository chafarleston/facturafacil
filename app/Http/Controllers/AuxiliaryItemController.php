<?php

namespace App\Http\Controllers;

use App\Models\AuxiliaryItem;
use App\Models\Company;
use Illuminate\Http\Request;

class AuxiliaryItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id ?? Company::first()->id;
        $items = AuxiliaryItem::where('company_id', $companyId)->orderBy('name')->get();
        return view('auxiliary-items.index', compact('items', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id ?? Company::first()->id;
        return view('auxiliary-items.create', compact('companyId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:100',
            'status' => 'nullable|in:ACTIVO,INACTIVO',
        ]);

        AuxiliaryItem::create($validated);

        return redirect()->route('auxiliary-items.index', ['company_id' => $validated['company_id']])
            ->with('success', 'Elemento auxiliar creado');
    }

    public function edit(AuxiliaryItem $auxiliaryItem)
    {
        return view('auxiliary-items.edit', compact('auxiliaryItem'));
    }

    public function update(Request $request, AuxiliaryItem $auxiliaryItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'status' => 'nullable|in:ACTIVO,INACTIVO',
        ]);

        $auxiliaryItem->update($validated);

        return redirect()->route('auxiliary-items.index', ['company_id' => $auxiliaryItem->company_id])
            ->with('success', 'Elemento auxiliar actualizado');
    }

    public function destroy(AuxiliaryItem $auxiliaryItem)
    {
        $companyId = $auxiliaryItem->company_id;
        $auxiliaryItem->delete();
        return redirect()->route('auxiliary-items.index', ['company_id' => $companyId])
            ->with('success', 'Elemento auxiliar eliminado');
    }

    public function list(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id ?? Company::first()->id;
        $items = AuxiliaryItem::where('company_id', $companyId)->active()->orderBy('name')->get(['id', 'name']);
        return response()->json($items);
    }
}
