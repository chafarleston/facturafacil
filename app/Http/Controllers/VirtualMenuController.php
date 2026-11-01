<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Setting;
use App\Services\VirtualPrebillQrService;
use App\Services\WebBrandingService;
use Illuminate\Http\Request;

class VirtualMenuController extends Controller
{
    private function authorizeSettings(): void
    {
        $this->authorize('permission', 'view_restaurant');
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
        $menuNames = array_pad(Setting::virtualMenuNames(), 5, '');
        $secondNames = array_pad(Setting::virtualMenuSecondNames(), 5, '');
        $menuPrices = array_pad(Setting::virtualMenuPrices(), 5, '');
        $enabled = Setting::isVirtualMenuEnabled();
        $domain = Setting::virtualMenuDomain();
        $publicUrl = $this->publicUrl();
        $qr = VirtualPrebillQrService::dataUri($publicUrl, 320);

        return view('virtualmenu.index', compact(
            'company',
            'menuNames',
            'secondNames',
            'menuPrices',
            'enabled',
            'domain',
            'publicUrl',
            'qr'
        ));
    }

    public function update(Request $request)
    {
        $this->authorizeSettings();

        $request->validate([
            'enabled' => 'nullable|boolean',
            'domain' => 'required|url|max:255',
            'menu_names' => 'nullable|array|max:5',
            'menu_names.*' => 'nullable|string|max:150',
            'second_names' => 'nullable|array|max:5',
            'second_names.*' => 'nullable|string|max:150',
            'menu_prices' => 'nullable|array|max:5',
            'menu_prices.*' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $menuNames = collect($request->input('menu_names', []))
            ->map(fn ($name) => trim((string) $name))
            ->take(5)
            ->values()
            ->all();
        while (count($menuNames) < 5) {
            $menuNames[] = '';
        }

        $secondNames = collect($request->input('second_names', []))
            ->map(fn ($name) => trim((string) $name))
            ->take(5)
            ->values()
            ->all();
        while (count($secondNames) < 5) {
            $secondNames[] = '';
        }

        $menuPrices = collect($request->input('menu_prices', []))
            ->map(fn ($price) => is_numeric($price) ? round((float) $price, 2) : 0)
            ->take(5)
            ->values()
            ->all();
        while (count($menuPrices) < 5) {
            $menuPrices[] = 0;
        }

        Setting::setVirtualMenuEnabled($request->boolean('enabled'));
        Setting::setVirtualMenuDomain($request->input('domain'));
        Setting::setVirtualMenuNames($menuNames);
        Setting::setVirtualMenuSecondNames($secondNames);
        Setting::setVirtualMenuPrices($menuPrices);

        return redirect()->route('virtual-menu.index')
            ->with('success', 'Menú Virtual actualizado correctamente.');
    }

    public function show()
    {
        if (! Setting::isVirtualMenuEnabled()) {
            abort(404);
        }

        $company = Company::getMainCompany();
        $menuNames = array_pad(Setting::virtualMenuNames(), 5, '');
        $secondNames = array_pad(Setting::virtualMenuSecondNames(), 5, '');
        $menuPrices = array_pad(Setting::virtualMenuPrices(), 5, 0);

        $menus = [];
        foreach ($menuNames as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $second = trim((string) ($secondNames[$index] ?? ''));

            $menus[] = [
                'number' => $index + 1,
                'main_name' => $name,
                'second_name' => $second !== '' ? $second : null,
                'price' => (float) ($menuPrices[$index] ?? 0),
            ];
        }

        $seconds = [];
        foreach ($secondNames as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $menuName = trim((string) ($menuNames[$index] ?? ''));

            $seconds[] = [
                'number' => $index + 1,
                'name' => $name,
                'menu_number' => $menuName !== '' ? $index + 1 : null,
            ];
        }

        $web = app(WebBrandingService::class)->for($company);

        return view('virtualmenu.show', compact('company', 'menus', 'seconds', 'web'));
    }
}
