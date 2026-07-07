<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $search = $request->get('search');
        $searchType = $request->get('search_type', 'descripcion');
        $filter = $request->get('filter', 'all');
        
        $products = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($filter === 'simple', fn($q) => $q->where('is_composite', false))
            ->when($filter === 'composite', fn($q) => $q->where('is_composite', true))
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

        return view('products.index', compact('products', 'companyId', 'filter'));
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
            'precio_compra' => 'nullable|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|numeric',
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
        $validated['precio_compra'] = $validated['precio_compra'] ?? 0;
        $validated['umedida_codigo'] = $validated['umedida_codigo'] ?? 'NIU';
        $validated['igv_percent'] = $validated['igv_percent'] ?? 18;

        Product::create($validated);

        Cache::forget('restaurant_products_' . $request->company_id);

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
            'precio_compra' => 'nullable|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
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

        Cache::forget('restaurant_products_' . $product->company_id);

        return redirect()->route('products.show', $product)->with('success', 'Producto actualizado');
    }

    public function destroy(Product $product)
    {
        $product->update(['estado' => 'INACTIVO']);
        Cache::forget('restaurant_products_' . $product->company_id);
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

    public function createComposite(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $nextNumber = $this->getNextProductCode($companyId);
        $codigo = 'PROD' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        
        $categories = Category::where('company_id', $companyId)->whereIn('estado', ['ACTIVO', 'ACT'])->get();
        
        $availableProducts = Product::where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->where('is_composite', false)
            ->get();
        
        return view('products.create_composite', compact('companyId', 'codigo', 'categories', 'availableProducts'));
    }

    public function storeComposite(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'codigo' => 'required|max:50',
            'codigo_barras' => 'nullable|max:50',
            'descripcion' => 'required',
            'codigo_sunat' => 'nullable|size:8',
            'umedida_codigo' => 'nullable|size:3',
            'precio' => 'required|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'kds_destination' => 'nullable|in:cocina,cocina2,bar',
            'components' => 'required|array|min:1',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->components as $component) {
            $componentProduct = Product::find($component['product_id']);
            if ($componentProduct->is_composite) {
                return back()->withErrors([
                    'components' => 'Un producto compuesto no puede ser componente de otro producto compuesto.'
                ])->withInput();
            }
        }

        $validated['stock'] = 0;
        $validated['is_composite'] = true;
        $validated['umedida_codigo'] = $validated['umedida_codigo'] ?? 'NIU';
        $validated['igv_percent'] = $validated['igv_percent'] ?? 18;

        $product = Product::create($validated);

        foreach ($request->components as $component) {
            $product->components()->create([
                'component_product_id' => $component['product_id'],
                'quantity' => $component['quantity'],
            ]);
        }

        Cache::forget('restaurant_products_' . $request->company_id);

        return redirect()->route('products.index', ['company_id' => $request->company_id])
            ->with('success', 'Producto compuesto creado correctamente');
    }

    public function editComposite(Product $product)
    {
        if (!$product->is_composite) {
            abort(404);
        }

        $categories = Category::where('company_id', $product->company_id)->whereIn('estado', ['ACTIVO', 'ACT'])->get();
        
        $availableProducts = Product::where('company_id', $product->company_id)
            ->where('estado', 'ACTIVO')
            ->where('is_composite', false)
            ->where('id', '!=', $product->id)
            ->get();

        $product->load('components.component');

        return view('products.edit_composite', compact('product', 'categories', 'availableProducts'));
    }

    public function updateComposite(Request $request, Product $product)
    {
        if (!$product->is_composite) {
            abort(404);
        }

        $validated = $request->validate([
            'codigo' => 'required|max:50',
            'codigo_barras' => 'nullable|max:50',
            'descripcion' => 'required',
            'codigo_sunat' => 'nullable|size:8',
            'umedida_codigo' => 'nullable|size:3',
            'precio' => 'required|numeric|min:0',
            'tipo_afectacion' => 'required|in:GRA,EXO,INA,EXE',
            'igv_percent' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'kds_destination' => 'nullable|in:cocina,cocina2,bar',
            'components' => 'required|array|min:1',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->components as $component) {
            $componentProduct = Product::find($component['product_id']);
            if ($componentProduct->is_composite) {
                return back()->withErrors([
                    'components' => 'Un producto compuesto no puede ser componente de otro producto compuesto.'
                ])->withInput();
            }
        }

        $product->update($validated);

        $product->components()->delete();
        foreach ($request->components as $component) {
            $product->components()->create([
                'component_product_id' => $component['product_id'],
                'quantity' => $component['quantity'],
            ]);
        }

        Cache::forget('restaurant_products_' . $product->company_id);

        return redirect()->route('products.show', $product)->with('success', 'Producto compuesto actualizado');
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
            'company_id' => 'required|exists:companies,id',
        ]);

        $tmpFile = $request->input('tmp_file');
        if ($tmpFile) {
            $tmpPath = storage_path('app/tmp/' . basename($tmpFile));
            if (!file_exists($tmpPath)) {
                return back()->with('error', 'El archivo temporal no existe. Vuelva a subir el archivo.');
            }
            $data = json_decode(file_get_contents($tmpPath), true);
            $rows = $data['rows'] ?? [];
            $colMap = $data['colMap'] ?? [];
            @unlink($tmpPath);
        } else {
            $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            $header = array_map('trim', $rows[0]);
            $headerLower = array_map('strtolower', $header);
            $rows = array_slice($rows, 1);
            $colMap = [
                'codigo' => $this->findColumn($headerLower, ['codigo', 'codigo_interno', 'code']),
                'codigo_barras' => $this->findColumn($headerLower, ['codigo_barras', 'barras', 'barcode', 'ean']),
                'descripcion' => $this->findColumn($headerLower, ['descripcion', 'descripcion', 'nombre', 'name', 'producto', 'detalle']),
                'precio' => $this->findColumn($headerLower, ['precio', 'price', 'pvp', 'precio_venta']),
                'stock' => $this->findColumn($headerLower, ['stock', 'cantidad', 'quantity']),
                'tipo_afectacion' => $this->findColumn($headerLower, ['tipo_afectacion', 'tipo_igv', 'afectacion']),
                'umedida' => $this->findColumn($headerLower, ['umedida', 'unidad', 'uom', 'medida']),
                'categoria' => $this->findColumn($headerLower, ['categoria', 'category', 'categoría']),
                'codigo_sunat' => $this->findColumn($headerLower, ['codigo_sunat', 'sunat', 'sunat_code']),
                'kds_destination' => $this->findColumn($headerLower, ['kds_destination', 'kds', 'destino_kds']),
            ];
        }

        $colDescripcion = $colMap['descripcion'];
        $colCodigo = $colMap['codigo'];
        $colCodigoBarras = $colMap['codigo_barras'];
        $colPrecio = $colMap['precio'];
        $colStock = $colMap['stock'];
        $colTipoAfectacion = $colMap['tipo_afectacion'];
        $colUndMedida = $colMap['umedida'];
        $colCategoria = $colMap['categoria'];
        $colCodigoSunat = $colMap['codigo_sunat'];
        $colKdsDest = $colMap['kds_destination'];

        if ($colDescripcion === null) {
            return back()->with('error', 'No se encontró la columna "descripcion" en el archivo');
        }

        $created = 0;
        $skipped = 0;
        $categoriesCreated = 0;
        $errors = [];
        $categoryCache = [];
        $dbMaxNum = $this->getNextProductCode($request->company_id);
        $fileMaxNum = 0;
        if ($colCodigo !== null) {
            foreach ($rows as $row) {
                $c = trim($row[$colCodigo] ?? '');
                if (preg_match('/^PROD(\d+)$/', strtoupper($c), $m)) {
                    $num = intval($m[1]);
                    if ($num >= $fileMaxNum) $fileMaxNum = $num + 1;
                }
            }
        }
        $autoCodeStart = max($dbMaxNum, $fileMaxNum);

        foreach ($rows as $i => $row) {
            if (empty($row[$colDescripcion] ?? '')) continue;

            try {
                $codigo = $colCodigo !== null ? trim($row[$colCodigo] ?? '') : '';
                if (empty($codigo)) {
                    $codigo = 'PROD' . str_pad($autoCodeStart, 5, '0', STR_PAD_LEFT);
                    $autoCodeStart++;
                }

                $descripcion = trim($row[$colDescripcion] ?? '');

                $existing = Product::where('company_id', $request->company_id)
                    ->where(function($q) use ($codigo, $descripcion) {
                        $q->where('codigo', $codigo)->orWhere('descripcion', $descripcion);
                    })->first();

                if ($existing && $existing->codigo === $codigo) {
                    $newCode = 'PROD' . str_pad($autoCodeStart, 5, '0', STR_PAD_LEFT);
                    $autoCodeStart++;
                    $codigo = $newCode;
                } elseif ($existing) {
                    $skipped++;
                    continue;
                }

                $precio = $colPrecio !== null ? floatval(str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $row[$colPrecio] ?? '0'))) : 0;
                $stock = $colStock !== null ? intval($row[$colStock] ?? 0) : 0;

                $tipoAfectacion = 'GRA';
                if ($colTipoAfectacion !== null) {
                    $val = strtoupper(trim($row[$colTipoAfectacion] ?? ''));
                    if (in_array($val, ['GRA', 'EXO', 'INA', 'EXE'])) $tipoAfectacion = $val;
                }

                $umedida = 'NIU';
                if ($colUndMedida !== null) {
                    $val = strtoupper(trim($row[$colUndMedida] ?? ''));
                    if (in_array($val, ['NIU', 'KGM', 'GRM', 'LTR', 'MLT', 'MTK', 'MTQ', 'HR', 'D', 'TNE', 'BX', 'PK'])) $umedida = $val;
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
                                ->where('nombre', $categoryName)->first();
                            if (!$category) {
                                $category = Category::create(['company_id' => $request->company_id, 'nombre' => $categoryName, 'estado' => 'ACT']);
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
                $errors[] = 'Fila ' . ($i + 2) . ': ' . $e->getMessage();
            }
        }

        $msg = "Importación completada: {$created} productos creados, {$skipped} omitidos (ya existen)";
        if ($categoriesCreated > 0) $msg .= ", {$categoriesCreated} categorías creadas";
        if (!empty($errors)) $msg .= '. Errores: ' . implode('; ', array_slice($errors, 0, 5));

        return redirect()->route('products.index', ['company_id' => $request->company_id])
            ->with('success', $msg);
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'company_id' => 'required|exists:companies,id',
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $allRows = $sheet->toArray();

        if (count($allRows) < 2) {
            return back()->with('error', 'El archivo debe contener al menos una fila de datos');
        }

        $header = array_map('trim', $allRows[0]);
        $headerLower = array_map('strtolower', $header);
        $rows = array_slice($allRows, 1);

        $colMap = [
            'codigo' => $this->findColumn($headerLower, ['codigo', 'codigo_interno', 'code']),
            'codigo_barras' => $this->findColumn($headerLower, ['codigo_barras', 'barras', 'barcode', 'ean']),
            'descripcion' => $this->findColumn($headerLower, ['descripcion', 'descripcion', 'nombre', 'name', 'producto', 'detalle']),
            'precio' => $this->findColumn($headerLower, ['precio', 'price', 'pvp', 'precio_venta']),
            'stock' => $this->findColumn($headerLower, ['stock', 'cantidad', 'quantity']),
            'tipo_afectacion' => $this->findColumn($headerLower, ['tipo_afectacion', 'tipo_igv', 'afectacion']),
            'umedida' => $this->findColumn($headerLower, ['umedida', 'unidad', 'uom', 'medida']),
            'categoria' => $this->findColumn($headerLower, ['categoria', 'category', 'categoría']),
            'codigo_sunat' => $this->findColumn($headerLower, ['codigo_sunat', 'sunat', 'sunat_code']),
            'kds_destination' => $this->findColumn($headerLower, ['kds_destination', 'kds', 'destino_kds']),
        ];

        $colDescripcion = $colMap['descripcion'];
        if ($colDescripcion === null) {
            return back()->with('error', 'No se encontró la columna "descripcion" en el archivo');
        }

        $previewRows = [];
        $validCount = 0;
        $errorCount = 0;
        $warningCount = 0;

        // Find the highest PROD code between DB and the uploaded file
        $dbMaxNum = $this->getNextProductCode($request->company_id);
        $fileMaxNum = 0;
        $colCodigo = $colMap['codigo'];
        foreach ($rows as $row) {
            if ($colCodigo !== null) {
                $c = trim($row[$colCodigo] ?? '');
                if (preg_match('/^PROD(\d+)$/', strtoupper($c), $m)) {
                    $num = intval($m[1]);
                    if ($num >= $fileMaxNum) $fileMaxNum = $num + 1;
                }
            }
        }
        $nextAutoCode = max($dbMaxNum, $fileMaxNum);

        foreach ($rows as $i => $row) {
            $descripcion = trim($row[$colDescripcion] ?? '');
            if (empty($descripcion)) {
                $errorCount++;
                $previewRows[] = ['row' => $i + 2, 'status' => 'error', 'message' => 'Descripción requerida'];
                continue;
            }

            $codigo = $colMap['codigo'] !== null ? trim($row[$colMap['codigo']] ?? '') : '';
            $precioStr = $colMap['precio'] !== null ? trim($row[$colMap['precio']] ?? '') : '';
            $stockStr = $colMap['stock'] !== null ? trim($row[$colMap['stock']] ?? '') : '';
            $tipo = $colMap['tipo_afectacion'] !== null ? strtoupper(trim($row[$colMap['tipo_afectacion']] ?? '')) : '';
            $umedida = $colMap['umedida'] !== null ? strtoupper(trim($row[$colMap['umedida']] ?? '')) : '';
            $codSunat = $colMap['codigo_sunat'] !== null ? trim($row[$colMap['codigo_sunat']] ?? '') : '';
            $cat = $colMap['categoria'] !== null ? trim($row[$colMap['categoria']] ?? '') : '';
            $kds = $colMap['kds_destination'] !== null ? trim($row[$colMap['kds_destination']] ?? '') : '';
            $barras = $colMap['codigo_barras'] !== null ? trim($row[$colMap['codigo_barras']] ?? '') : '';

            $warnings = [];
            $errors = [];

            $precioVal = $colMap['precio'] !== null ? floatval(str_replace(',', '.', preg_replace('/[^0-9.,]/', '', $precioStr))) : 0;
            if (empty($precioStr) || $precioVal <= 0) {
                $errors[] = 'Precio requerido o inválido';
            }

            $stockVal = $colMap['stock'] !== null ? intval(preg_replace('/[^0-9]/', '', $stockStr)) : 0;

            if (empty($codSunat)) {
                $errors[] = 'Código SUNAT requerido';
            } elseif (strlen($codSunat) !== 8) {
                $errors[] = 'Código SUNAT debe tener 8 dígitos';
            }

            if (!empty($tipo) && !in_array($tipo, ['GRA', 'EXO', 'INA', 'EXE'])) {
                $warnings[] = "Tipo '$tipo' inválido, se usará GRA";
            }
            $umedidasValidas = ['NIU', 'KGM', 'GRM', 'LTR', 'MLT', 'MTK', 'MTQ', 'HR', 'D', 'TNE', 'BX', 'PK'];
            if (!empty($umedida) && !in_array($umedida, $umedidasValidas)) {
                $warnings[] = "U.Medida '$umedida' inválida, se usará NIU";
            }
            $kdsValidos = ['cocina', 'cocina2', 'bar'];
            if (!empty($kds) && !in_array($kds, $kdsValidos)) {
                $warnings[] = "Destino KDS '$kds' inválido, se usará cocina";
            }

            $productExists = Product::where('company_id', $request->company_id)
                ->where('codigo', $codigo)->exists();
            if (!empty($codigo) && $productExists) {
                $newCode = 'PROD' . str_pad($nextAutoCode, 5, '0', STR_PAD_LEFT);
                $nextAutoCode++;
                $warnings[] = "Código '$codigo' ya existe, se usará '$newCode'";
                $codigo = $newCode;
            }

            if (count($errors) > 0) {
                $status = 'error';
                $errorCount++;
                $message = implode('; ', $errors);
            } elseif (count($warnings) > 0) {
                $status = 'warning';
                $warningCount++;
                $message = implode('; ', $warnings);
            } else {
                $status = 'valid';
                $validCount++;
                $message = '';
            }

            $previewRows[] = [
                'row' => $i + 2,
                'codigo' => $codigo ?: '(auto)',
                'codigo_barras' => $barras,
                'descripcion' => $descripcion,
                'precio' => $precioVal,
                'stock' => $stockVal,
                'tipo_afectacion' => $tipo ?: 'GRA',
                'umedida' => $umedida ?: 'NIU',
                'categoria' => $cat,
                'codigo_sunat' => $codSunat,
                'kds_destination' => $kds ?: 'cocina',
                'status' => $status,
                'message' => implode('; ', $warnings),
            ];
        }

        // Save parsed data to temp file
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
        $tmpName = 'import_' . uniqid() . '.json';
        file_put_contents($tmpDir . '/' . $tmpName, json_encode([
            'rows' => $rows,
            'colMap' => $colMap,
            'header' => $header,
        ]));

        return view('products.preview', [
            'companyId' => $request->company_id,
            'previewRows' => $previewRows,
            'total' => count($previewRows),
            'validCount' => $validCount,
            'errorCount' => $errorCount,
            'warningCount' => $warningCount,
            'tmpFile' => $tmpName,
        ]);
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

    public function inventoryReport(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $categoryId = $request->get('category_id');
        
        $categories = Category::where('company_id', $companyId)
            ->whereIn('estado', ['ACTIVO', 'ACT'])
            ->orderBy('nombre')
            ->get();
        
        $products = Product::with('category')
            ->where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('descripcion')
            ->get();
        
        $totalProductos = $products->count();
        $totalStock = $products->sum('stock');
        $totalValorVenta = $products->sum(fn($p) => $p->stock * $p->precio);
        $totalValorCosto = $products->sum(fn($p) => $p->stock * $p->precio_compra);
        
        return view('products.inventory_report', compact(
            'products', 'categories', 'categoryId', 'companyId',
            'totalProductos', 'totalStock', 'totalValorVenta', 'totalValorCosto'
        ));
    }

    public function inventoryReportExcel(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $categoryId = $request->get('category_id');
        
        $products = Product::with('category')
            ->where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('descripcion')
            ->get();
        
        $data = [['Código', 'Descripción', 'Categoría', 'Stock', 'Precio Compra', 'Precio Venta', 'Valor Total (Venta)', 'Valor Total (Costo)']];
        
        foreach ($products as $p) {
            $data[] = [
                $p->codigo,
                $p->descripcion,
                $p->category->nombre ?? 'Sin categoría',
                $p->stock,
                $p->precio_compra,
                $p->precio,
                $p->stock * $p->precio,
                $p->stock * $p->precio_compra,
            ];
        }
        
        return $this->exportSpreadsheet($data, 'inventario_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function inventoryReportPdf(Request $request)
    {
        $companyId = $request->company_id ?? Company::first()->id;
        $categoryId = $request->get('category_id');
        
        $products = Product::with('category')
            ->where('company_id', $companyId)
            ->where('estado', 'ACTIVO')
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('descripcion')
            ->get();
        
        $totalProductos = $products->count();
        $totalStock = $products->sum('stock');
        $totalValorVenta = $products->sum(fn($p) => $p->stock * $p->precio);
        $totalValorCosto = $products->sum(fn($p) => $p->stock * $p->precio_compra);
        
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
        
        $html = view('products.inventory_report_pdf', compact(
            'products', 'totalProductos', 'totalStock', 
            'totalValorVenta', 'totalValorCosto'
        ))->render();
        
        $pdf->WriteHTML($html);
        
        return $pdf->Output('inventario_' . now()->format('Ymd_His') . '.pdf', 'D');
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
