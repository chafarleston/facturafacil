<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);
        $categories = Category::where('company_id', $companyId)->orderBy('nombre')->get();
        
        return view('categories.index', compact('categories', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);
        return view('categories.create', compact('companyId'));
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $companyId = $request->get('company_id', \App\Models\Company::getMainCompany()->id);

        $data = [
            'company_id' => $companyId,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? 'ACT',
        ];

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $imageOptimizer->optimize($request->file('imagen'), 'categories');
        }

        Category::create($data);

        $this->clearCategoryCaches($companyId);

        return redirect()->route('categories.index', ['company_id' => $companyId])
            ->with('success', 'Categoría creada correctamente');
    }

    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category, ImageOptimizer $imageOptimizer)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? $category->estado,
        ];

        if ($request->hasFile('imagen')) {
            if ($category->imagen) {
                Storage::disk('public')->delete($category->imagen);
            }
            $data['imagen'] = $imageOptimizer->optimize($request->file('imagen'), 'categories');
        } elseif ($request->boolean('remove_imagen')) {
            if ($category->imagen) {
                Storage::disk('public')->delete($category->imagen);
            }
            $data['imagen'] = null;
        }

        $category->update($data);

        $this->clearCategoryCaches($category->company_id);

        return redirect()->route('categories.index', ['company_id' => $category->company_id])
            ->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy(Category $category)
    {
        $companyId = $category->company_id;
        $category->delete();

        $this->clearCategoryCaches($companyId);

        return redirect()->route('categories.index', ['company_id' => $companyId])
            ->with('success', 'Categoría eliminada correctamente');
    }

    private function clearCategoryCaches($companyId): void
    {
        Cache::forget('restaurant_products_' . $companyId);
        Cache::forget('restaurant_categories_' . $companyId);
        Cache::forget('kiosko_products_' . $companyId);
        Cache::forget('kiosko_categories_' . $companyId);
    }
}