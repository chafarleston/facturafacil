<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Company;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $floors = Floor::where('company_id', $companyId)
            ->ordered()
            ->with('tables')
            ->get();

        return view('restaurant.floors.index', compact('floors', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        return view('restaurant.floors.create', compact('companyId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|max:100',
            'order' => 'nullable|integer',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        $maxOrder = Floor::where('company_id', $request->company_id)->max('order') ?? 0;
        $validated['order'] = $request->order ?? ($maxOrder + 1);

        Floor::create($validated);

        return redirect()->route('restaurant.floors.index', ['company_id' => $request->company_id])
            ->with('success', 'Piso creado exitosamente');
    }

    public function edit(Floor $floor)
    {
        $tables = $floor->tables()->orderBy('id')->get();
        return view('restaurant.floors.edit', compact('floor', 'tables'));
    }

    public function update(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'order' => 'nullable|integer',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        $floor->update($validated);

        return redirect()->route('restaurant.floors.index', ['company_id' => $floor->company_id])
            ->with('success', 'Piso actualizado');
    }

    public function destroy(Floor $floor)
    {
        $companyId = $floor->company_id;
        $floor->delete();

        return redirect()->route('restaurant.floors.index', ['company_id' => $companyId])
            ->with('success', 'Piso eliminado');
    }
}
