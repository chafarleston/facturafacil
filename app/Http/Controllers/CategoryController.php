<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        $categories = Category::where('company_id', $companyId)->orderBy('nombre')->get();
        
        return view('categories.index', compact('categories', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->get('company_id', Auth::user()->company_id);
        return view('categories.create', compact('companyId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $companyId = $request->get('company_id', Auth::user()->company_id);
        
        Category::create([
            'company_id' => $companyId,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? 'ACT',
        ]);

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

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $category->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? $category->estado,
        ]);

        return redirect()->route('categories.index', ['company_id' => $category->company_id])
            ->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy(Category $category)
    {
        $companyId = $category->company_id;
        $category->delete();

        return redirect()->route('categories.index', ['company_id' => $companyId])
            ->with('success', 'Categoría eliminada correctamente');
    }
}