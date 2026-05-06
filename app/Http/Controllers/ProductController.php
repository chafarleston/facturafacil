<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $search = $request->get('search');
        
        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('codigo', 'like', "%{$search}%")
                          ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return view('products.index', compact('products', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id;
        $lastProduct = Product::where('company_id', $companyId)->orderBy('id', 'desc')->first();
        $nextNumber = $lastProduct ? (int)substr($lastProduct->codigo, -5) + 1 : 1;
        $codigo = 'PROD' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        
        $categories = Category::where('company_id', $companyId)->where('estado', 'ACT')->get();
        
        return view('products.create', compact('companyId', 'codigo', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'codigo' => 'required|max:50',
            'descripcion' => 'required',
            'codigo_sunat' => 'nullable|size:8',
            'umedida_codigo' => 'nullable|size:3',
            'precio' => 'nullable|numeric|min:0',
            'precio_minimo' => 'nullable|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
        ]);

        if (is_null($validated['precio'] ?? null)) {
            if ($request->input('precio_con_igv') !== null) {
                $validated['precio'] = $request->input('precio_con_igv');
            } elseif ($request->input('precio_sin_igv') !== null) {
                $validated['precio'] = $request->input('precio_sin_igv');
            } else {
                $validated['precio'] = 0;
            }
        }

        $validated['stock'] = $validated['stock'] ?? 0;
        $validated['umedida_codigo'] = $validated['umedida_codigo'] ?? 'NIU';
        $validated['igv_percent'] = $validated['igv_percent'] ?? 18;

        Product::create($validated);

        return redirect()->route('products.index', ['company_id' => $request->company_id])
            ->with('success', 'Producto creado correctamente');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('company_id', $product->company_id)->where('estado', 'ACT')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'codigo' => 'required|max:50',
            'descripcion' => 'required',
            'codigo_sunat' => 'nullable|size:8',
            'umedida_codigo' => 'nullable|size:3',
            'precio' => 'nullable|numeric|min:0',
            'precio_minimo' => 'nullable|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
        ]);

        if (is_null($validated['precio'] ?? null)) {
            if ($request->input('precio_con_igv') !== null) {
                $validated['precio'] = $request->input('precio_con_igv');
            } elseif ($request->input('precio_sin_igv') !== null) {
                $validated['precio'] = $request->input('precio_sin_igv');
            } else {
                $validated['precio'] = 0;
            }
        }

        $product->update($validated);

        return redirect()->route('products.show', $product)->with('success', 'Producto actualizado');
    }

    public function destroy(Product $product)
    {
        $product->update(['estado' => 'INACTIVO']);
        return back()->with('success', 'Producto desactivado');
    }
}
