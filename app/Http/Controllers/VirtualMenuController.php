<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Setting;
use App\Services\WebBrandingService;
use Illuminate\Http\Request;

class VirtualMenuController extends Controller
{
    private function authorizeSettings(): void
    {
        $this->authorize('permission', 'view_restaurant');
    }

    private function productQuery(Company $company)
    {
        return Product::where('company_id', $company->id)
            ->where('estado', 'ACTIVO')
            ->orderBy('descripcion');
    }

    private function publicUrl(): string
    {
        $domain = Setting::virtualMenuDomain();

        return $domain !== ''
            ? rtrim($domain, '/') . '/menuvirtual'
            : url('/menuvirtual');
    }

    public function index()
    {
        $this->authorizeSettings();

        $company = Company::getMainCompany();
        $products = $this->productQuery($company)->get(['id', 'descripcion', 'precio']);
        $menuIds = Setting::virtualMenuProducts();
        $secondIds = Setting::virtualMenuSeconds();
        $enabled = Setting::isVirtualMenuEnabled();
        $domain = Setting::virtualMenuDomain();
        $publicUrl = $this->publicUrl();

        return view('virtualmenu.index', compact(
            'company',
            'products',
            'menuIds',
            'secondIds',
            'enabled',
            'domain',
            'publicUrl'
        ));
    }

    public function update(Request $request)
    {
        $this->authorizeSettings();

        $request->validate([
            'enabled' => 'nullable|boolean',
            'domain' => 'required|url|max:255',
            'menu_products' => 'nullable|array|max:5',
            'menu_products.*' => 'nullable|integer|exists:products,id',
            'second_products' => 'nullable|array|max:4',
            'second_products.*' => 'nullable|integer|exists:products,id',
        ]);

        $company = Company::getMainCompany();
        $allowed = $this->productQuery($company)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $menuIds = collect($request->input('menu_products', []))
            ->map(fn ($id) => $id && in_array((int) $id, $allowed, true) ? (int) $id : 0)
            ->take(5)
            ->values()
            ->all();

        $secondIds = collect($request->input('second_products', []))
            ->map(fn ($id) => $id && in_array((int) $id, $allowed, true) ? (int) $id : 0)
            ->take(4)
            ->values()
            ->all();

        Setting::setVirtualMenuEnabled($request->boolean('enabled'));
        Setting::setVirtualMenuDomain($request->input('domain'));
        Setting::setVirtualMenuProducts($menuIds);
        Setting::setVirtualMenuSeconds($secondIds);

        return redirect()->route('virtual-menu.index')
            ->with('success', 'Menú Virtual actualizado correctamente.');
    }

    public function show()
    {
        if (! Setting::isVirtualMenuEnabled()) {
            abort(404);
        }

        $company = Company::getMainCompany();
        $menuIds = Setting::virtualMenuProducts();
        $secondIds = Setting::virtualMenuSeconds();
        $ids = array_values(array_unique(array_filter(array_merge($menuIds, $secondIds))));

        $products = Product::where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->where('estado', 'ACTIVO')
            ->get()
            ->keyBy('id');

        $menus = [];
        foreach ($menuIds as $index => $id) {
            $main = $products->get((int) $id);
            if (! $main) {
                continue;
            }

            $second = $products->get((int) ($secondIds[$index] ?? 0));
            $menus[] = [
                'number' => $index + 1,
                'main' => $main,
                'second' => $second,
                'total' => (float) $main->precio + (float) ($second->precio ?? 0),
            ];
        }

        $web = app(WebBrandingService::class)->for($company);

        return view('virtualmenu.show', compact('company', 'menus', 'web'));
    }
}
