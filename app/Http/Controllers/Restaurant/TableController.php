<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\Floor;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    /**
     * Slugs reservados para no chocar con rutas del sistema.
     */
    private const RESERVED_SLUGS = [
        'login', 'logout', 'autopedido', 'dashboard', 'pos', 'restaurant',
        'restaurant-settings', 'virtual-prebill', 'kiosk-settings', 'catalog-images',
        'companies', 'products', 'categories', 'customers', 'cashregisters',
        'cash-movements', 'cash-report-settings', 'users', 'roles', 'permissions',
        'series', 'printers', 'personal', 'schedules', 'attendance', 'marcar', 'up',
    ];

    /**
     * Genera un slug único por empresa a partir del nombre de la mesa.
     */
    private function generateSlug(string $name, int $companyId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '');
        if ($base === '') {
            $base = 'mesa' . uniqid();
        }

        if (in_array($base, self::RESERVED_SLUGS, true)) {
            $base .= '-mesa';
        }

        $slug = $base;
        $i = 2;

        while (RestaurantTable::where('company_id', $companyId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
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
        ], [
            'name.unique' => 'Ya existe una mesa con ese nombre en este piso.',
        ]);

        $validated['capacity'] = $request->capacity ?? 4;
        $validated['color'] = $request->color ?? '#28a745';
        $validated['slug'] = $this->generateSlug($validated['name'], $validated['company_id']);

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
        ], [
            'name.unique' => 'Ya existe una mesa con ese nombre en este piso.',
        ]);

        // El slug se mantiene estable (no cambia si se renombra la mesa)
        if (empty($restaurantTable->slug)) {
            $validated['slug'] = $this->generateSlug($validated['name'], $restaurantTable->company_id, $restaurantTable->id);
        }

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
