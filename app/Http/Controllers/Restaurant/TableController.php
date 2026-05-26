<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\Floor;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('restaurant.floors.index', ['company_id' => $request->company_id ?? Company::first()->id]);
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $floors = Floor::where('company_id', $companyId)->active()->ordered()->get();
        $floorId = $request->floor_id;
        
        if ($floors->isEmpty()) {
            return redirect()->route('restaurant.floors.index', ['company_id' => $companyId])
                ->with('error', 'Primero debe crear al menos un piso');
        }

        return view('restaurant.tables.create', compact('floors', 'companyId', 'floorId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'floor_id' => 'required|exists:floors,id',
            'name' => ['required', 'max:50', Rule::unique('restaurant_tables')->where(fn($q) => $q->where('floor_id', $request->floor_id))],
            'capacity' => 'nullable|integer|min:1',
            'color' => 'nullable|max:20',
        ]);

        $validated['capacity'] = $request->capacity ?? 4;
        $validated['color'] = $request->color ?? '#28a745';

        RestaurantTable::create($validated);

        return redirect()->route('restaurant.floors.index', ['company_id' => $request->company_id])
            ->with('success', 'Mesa creada exitosamente');
    }

    public function edit(RestaurantTable $restaurantTable)
    {
        $floors = Floor::where('company_id', $restaurantTable->company_id)->active()->ordered()->get();
        return view('restaurant.tables.edit', compact('restaurantTable', 'floors'));
    }

    public function update(Request $request, RestaurantTable $restaurantTable)
    {
        $validated = $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'name' => ['required', 'max:50', Rule::unique('restaurant_tables')->where(fn($q) => $q->where('floor_id', $request->floor_id))->ignore($restaurantTable->id)],
            'capacity' => 'nullable|integer|min:1',
            'color' => 'nullable|max:20',
            'status' => 'nullable|in:AVAILABLE,OCCUPIED,RESERVED',
        ]);

        $restaurantTable->update($validated);

        return redirect()->route('restaurant.floors.index', ['company_id' => $restaurantTable->company_id])
            ->with('success', 'Mesa actualizada');
    }

    public function destroy(RestaurantTable $restaurantTable)
    {
        if ($restaurantTable->activeOrder()) {
            return back()->with('error', 'No se puede eliminar una mesa con pedidos activos');
        }

        $companyId = $restaurantTable->company_id;
        $restaurantTable->delete();

        return redirect()->route('restaurant.floors.index', ['company_id' => $companyId])
            ->with('success', 'Mesa eliminada');
    }
}
