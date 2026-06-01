<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\StockOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutputController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id ?? Company::first()->id;

        $outputs = StockOutput::with(['user', 'items.product'])
            ->where('company_id', $companyId)
            ->withTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock-outputs.index', compact('outputs', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id ?? Company::getMainCompany()?->id ?? Company::first()->id;

        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->orderBy('descripcion')
            ->get(['id', 'codigo', 'descripcion', 'umedida_codigo', 'stock']);

        return view('stock-outputs.create', compact('products', 'companyId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'motivo' => 'required|in:consumo_cocina,merma,degustacion,otro',
            'motivo_otro' => 'required_if:motivo,otro|nullable|string|max:255',
            'referencia' => 'nullable|string|max:100',
            'notas' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cantidad' => 'required|numeric|min:0.001',
        ], [
            'items.required' => 'Agregue al menos un producto',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
        ]);

        $company = Company::findOrFail($validated['company_id']);

        DB::transaction(function () use ($validated, $company) {
            $output = StockOutput::create([
                'company_id' => $company->id,
                'user_id' => auth()->id(),
                'motivo' => $validated['motivo'],
                'motivo_otro' => $validated['motivo_otro'],
                'referencia' => $validated['referencia'],
                'notas' => $validated['notas'],
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $stockAntes = (float) $product->stock;
                $cantidad = (float) $item['cantidad'];
                $stockDespues = max(0, $stockAntes - $cantidad);

                $output->items()->create([
                    'product_id' => $product->id,
                    'cantidad' => $cantidad,
                    'stock_antes' => $stockAntes,
                    'stock_despues' => $stockDespues,
                ]);

                $product->update(['stock' => $stockDespues]);
            }
        });

        return redirect()->route('stock-outputs.index')
            ->with('success', 'Consumo registrado correctamente');
    }

    public function show(StockOutput $stockOutput)
    {
        $stockOutput->load(['user', 'items.product', 'company']);
        return view('stock-outputs.show', compact('stockOutput'));
    }

    public function edit(StockOutput $stockOutput)
    {
        if ($stockOutput->trashed()) {
            return redirect()->route('stock-outputs.index')
                ->with('error', 'No se puede editar un consumo eliminado');
        }

        $companyId = $stockOutput->company_id;
        $stockOutput->load('items.product');

        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->orderBy('descripcion')
            ->get(['id', 'codigo', 'descripcion', 'umedida_codigo', 'stock']);

        return view('stock-outputs.edit', compact('stockOutput', 'products', 'companyId'));
    }

    public function update(Request $request, StockOutput $stockOutput)
    {
        if ($stockOutput->trashed()) {
            return redirect()->route('stock-outputs.index')
                ->with('error', 'No se puede editar un consumo eliminado');
        }

        $validated = $request->validate([
            'motivo' => 'required|in:consumo_cocina,merma,degustacion,otro',
            'motivo_otro' => 'required_if:motivo,otro|nullable|string|max:255',
            'referencia' => 'nullable|string|max:100',
            'notas' => 'nullable|string|max:500',
        ]);

        $stockOutput->update($validated);

        return redirect()->route('stock-outputs.show', $stockOutput)
            ->with('success', 'Consumo actualizado correctamente');
    }

    public function destroy(StockOutput $stockOutput)
    {
        if ($stockOutput->trashed()) {
            return redirect()->route('stock-outputs.index')
                ->with('error', 'Este consumo ya fue eliminado');
        }

        DB::transaction(function () use ($stockOutput) {
            $stockOutput->load('items.product');

            foreach ($stockOutput->items as $item) {
                if ($item->product) {
                    $product = $item->product;
                    $stockActual = (float) $product->stock;
                    $cantidad = (float) $item->cantidad;
                    $product->update(['stock' => $stockActual + $cantidad]);
                }
            }

            $stockOutput->delete();
        });

        return redirect()->route('stock-outputs.index')
            ->with('success', 'Consumo anulado. Stock reincorporado.');
    }
}
