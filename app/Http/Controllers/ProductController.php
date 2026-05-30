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
        $searchType = $request->get('search_type', 'descripcion');
        
        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($search, function($q) use ($search, $searchType) {
                if ($searchType === 'categoria') {
                    $q->whereHas('category', function($cq) use ($search) {
                        $cq->where('nombre', 'like', "%{$search}%");
                    });
                } else {
                    $q->where($searchType, 'like', "%{$search}%");
                }
            })
            ->paginate(15);

        return view('products.index', compact('products', 'companyId'));
    }

    public function create(Request $request)
    {
        $companyId = $request->company_id;
        $lastProduct = Product::where('company_id', $companyId)->orderBy('id', 'desc')->first();
        $nextNumber = $this->getNextProductCode($companyId);
        $codigo = 'PROD' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        
        $categories = Category::where('company_id', $companyId)->whereIn('estado', ['ACTIVO', 'ACT'])->get();
        
        return view('products.create', compact('companyId', 'codigo', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'codigo' => 'required|max:50',
            'codigo_barras' => 'nullable|max:50',
            'descripcion' => 'required',
            'codigo_sunat' => 'nullable|size:8',
            'umedida_codigo' => 'nullable|size:3',
            'precio' => 'nullable|numeric|min:0',
            'precio_minimo' => 'nullable|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
            'kds_destination' => 'nullable|in:cocina,cocina2,bar',
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
        $prev = Product::where('company_id', $product->company_id)
            ->where('id', '<', $product->id)
            ->orderBy('id', 'desc')
            ->first();

        $next = Product::where('company_id', $product->company_id)
            ->where('id', '>', $product->id)
            ->orderBy('id', 'asc')
            ->first();

        return view('products.show', compact('product', 'prev', 'next'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('company_id', $product->company_id)->whereIn('estado', ['ACTIVO', 'ACT'])->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'codigo' => 'required|max:50',
            'codigo_barras' => 'nullable|max:50',
            'descripcion' => 'required',
            'codigo_sunat' => 'nullable|size:8',
            'umedida_codigo' => 'nullable|size:3',
            'precio' => 'nullable|numeric|min:0',
            'precio_minimo' => 'nullable|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
            'kds_destination' => 'nullable|in:cocina,cocina2,bar',
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

    public function duplicate(Request $request, Product $product)
    {
        $companyId = $product->company_id;
        $nextNumber = $this->getNextProductCode($companyId);
        $newCodigo = 'PROD' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $duplicate = Product::create([
            'company_id' => $companyId,
            'codigo' => $newCodigo,
            'codigo_barras' => $product->codigo_barras,
            'descripcion' => $product->descripcion . ' (Duplicado)',
            'codigo_sunat' => $product->codigo_sunat,
            'umedida_codigo' => $product->umedida_codigo,
            'precio' => $product->precio,
            'precio_minimo' => $product->precio_minimo,
            'tipo_afectacion' => $product->tipo_afectacion,
            'igv_percent' => $product->igv_percent,
            'estado' => 'ACTIVO',
            'category_id' => $product->category_id,
            'stock' => 0,
            'kds_destination' => $product->kds_destination,
        ]);

        return redirect()->route('products.edit', $duplicate)
            ->with('success', 'Producto duplicado correctamente. Revise los datos.');
    }

    public function importForm(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $categories = Category::where('company_id', $companyId)->where('estado', 'ACT')->get();
        return view('products.import', compact('companyId', 'categories'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'company_id' => 'required|exists:companies,id',
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return back()->with('error', 'El archivo debe contener al menos una fila de datos');
        }

        $header = array_map('trim', $rows[0]);
        $headerLower = array_map('strtolower', $header);

        $colCodigo = $this->findColumn($headerLower, ['codigo', 'codigo_interno', 'code']);
        $colCodigoBarras = $this->findColumn($headerLower, ['codigo_barras', 'barras', 'barcode', 'ean']);
        $colDescripcion = $this->findColumn($headerLower, ['descripcion', 'descripcion', 'nombre', 'name', 'producto', 'detalle']);
        $colPrecio = $this->findColumn($headerLower, ['precio', 'price', 'pvp', 'precio_venta']);
        $colStock = $this->findColumn($headerLower, ['stock', 'cantidad', 'quantity']);
        $colTipoAfectacion = $this->findColumn($headerLower, ['tipo_afectacion', 'tipo_igv', 'afectacion']);
        $colUndMedida = $this->findColumn($headerLower, ['umedida', 'unidad', 'uom', 'medida']);
        $colCategoria = $this->findColumn($headerLower, ['categoria', 'category', 'categoría']);
        $colCodigoSunat = $this->findColumn($headerLower, ['codigo_sunat', 'sunat', 'sunat_code']);
        $colKdsDest = $this->findColumn($headerLower, ['kds_destination', 'kds', 'destino_kds']);

        if ($colDescripcion === null) {
            return back()->with('error', 'No se encontró la columna "descripcion" en el archivo');
        }

        $created = 0;
        $skipped = 0;
        $categoriesCreated = 0;
        $errors = [];

        $categoryCache = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row[$colDescripcion])) {
                continue;
            }

            try {
                $codigo = $colCodigo !== null ? trim($row[$colCodigo] ?? '') : '';
                if (empty($codigo)) {
                    static $autoCodeStart = null;
                    if ($autoCodeStart === null) {
                        $autoCodeStart = $this->getNextProductCode($request->company_id);
                    }
                    $codigo = 'PROD' . str_pad($autoCodeStart + $i, 5, '0', STR_PAD_LEFT);
                }

                $descripcion = trim($row[$colDescripcion] ?? '');
                
                $existing = Product::where('company_id', $request->company_id)
                    ->where(function($q) use ($codigo, $descripcion) {
                        $q->where('codigo', $codigo)->orWhere('descripcion', $descripcion);
                    })->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $precio = $colPrecio !== null ? floatval($row[$colPrecio] ?? 0) : 0;
                $stock = $colStock !== null ? intval($row[$colStock] ?? 0) : 0;

                $tipoAfectacion = 'GRA';
                if ($colTipoAfectacion !== null) {
                    $val = strtoupper(trim($row[$colTipoAfectacion] ?? ''));
                    if (in_array($val, ['GRA', 'EXO', 'INA', 'EXE'])) {
                        $tipoAfectacion = $val;
                    }
                }

                $umedida = 'NIU';
                if ($colUndMedida !== null) {
                    $val = strtoupper(trim($row[$colUndMedida] ?? ''));
                    if (in_array($val, ['NIU', 'KGM', 'GRM', 'LTR', 'MLT', 'MTK', 'MTQ', 'HR', 'D', 'TNE', 'BX', 'PK'])) {
                        $umedida = $val;
                    }
                }

                $categoryId = null;
                if ($colCategoria !== null) {
                    $categoryName = trim($row[$colCategoria] ?? '');
                    if (!empty($categoryName)) {
                        $categoryKey = strtoupper($categoryName);
                        
                        if (isset($categoryCache[$categoryKey])) {
                            $categoryId = $categoryCache[$categoryKey];
                        } else {
                            $category = Category::where('company_id', $request->company_id)
                                ->where('nombre', $categoryName)
                                ->first();
                            
                            if (!$category) {
                                $category = Category::create([
                                    'company_id' => $request->company_id,
                                    'nombre' => $categoryName,
                                    'estado' => 'ACT',
                                ]);
                                $categoriesCreated++;
                            }
                            
                            $categoryId = $category->id;
                            $categoryCache[$categoryKey] = $categoryId;
                        }
                    }
                }

                Product::create([
                    'company_id' => $request->company_id,
                    'codigo' => $codigo,
                    'codigo_barras' => $colCodigoBarras !== null ? trim($row[$colCodigoBarras] ?? '') : null,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'stock' => $stock,
                    'tipo_afectacion' => $tipoAfectacion,
                    'umedida_codigo' => $umedida,
                    'igv_percent' => 18,
                    'estado' => 'ACTIVO',
                    'category_id' => $categoryId,
                    'codigo_sunat' => $colCodigoSunat !== null ? trim($row[$colCodigoSunat] ?? '') : null,
                    'kds_destination' => $colKdsDest !== null ? trim($row[$colKdsDest] ?? '') : 'cocina',
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = 'Fila ' . ($i + 1) . ': ' . $e->getMessage();
            }
        }

        $msg = "Importación completada: {$created} productos creados, {$skipped} omitidos (ya existen)";
        if ($categoriesCreated > 0) {
            $msg .= ", {$categoriesCreated} categorías creadas";
        }
        if (!empty($errors)) {
            $msg .= '. Errores: ' . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('products.index', ['company_id' => $request->company_id])
            ->with('success', $msg);
    }

    private function findColumn(array $header, array $names): ?int
    {
        foreach ($names as $name) {
            $idx = array_search($name, $header);
            if ($idx !== false) {
                return $idx;
            }
        }
        return null;
    }

    public function downloadTemplate()
    {
        return $this->exportSpreadsheet([
            ['codigo', 'codigo_barras', 'descripcion', 'precio', 'stock', 'tipo_afectacion', 'umedida', 'categoria', 'codigo_sunat', 'kds_destination'],
        ], 'plantilla_productos.xlsx');
    }

    public function export(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $products = Product::with('category')
            ->where('company_id', $companyId)
            ->where('estado', '!=', 'INACTIVO')
            ->orderBy('descripcion')
            ->get();

        $data = [['Código', 'Cód. Barras', 'Descripción', 'Categoría', 'Precio', 'Stock', 'Tipo', 'U.Medida', 'IGV %', 'Destino KDS', 'Estado']];

        foreach ($products as $p) {
            $data[] = [
                $p->codigo,
                $p->codigo_barras ?? '',
                $p->descripcion,
                $p->category->nombre ?? '',
                $p->precio,
                $p->stock,
                $p->tipo_afectacion ?? 'GRA',
                $p->umedida_codigo ?? 'NIU',
                $p->igv_percent,
                $p->kds_destination ?? '',
                $p->estado,
            ];
        }

        return $this->exportSpreadsheet($data, 'productos_' . now()->format('Ymd_His') . '.xlsx');
    }

    private function exportSpreadsheet(array $data, string $filename)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function getNextProductCode(int $companyId): int
    {
        $maxCodigo = Product::where('company_id', $companyId)
            ->where('codigo', 'like', 'PROD%')
            ->orderByRaw('CAST(SUBSTRING(codigo, 5) AS UNSIGNED) DESC')
            ->value('codigo');
        return $maxCodigo ? (int)substr($maxCodigo, -5) + 1 : 1;
    }
}
