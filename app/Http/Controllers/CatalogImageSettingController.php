<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class CatalogImageSettingController extends Controller
{
    public const MODULES = [
        'pos' => 'Punto de Venta',
        'restaurant' => 'Restaurante',
        'kiosko' => 'Kiosko (Autopedido)',
    ];

    public function edit()
    {
        $modules = self::MODULES;
        $productImages = [];
        $categoryImages = [];
        $categoriesOnly = [];

        foreach (array_keys($modules) as $module) {
            $productImages[$module] = Setting::showProductImages($module);
            $categoryImages[$module] = Setting::showCategoryImages($module);
            $categoriesOnly[$module] = Setting::categoriesOnly($module);
        }

        return view('catalogimages.edit', compact('modules', 'productImages', 'categoryImages', 'categoriesOnly'));
    }

    public function update(Request $request)
    {
        foreach (array_keys(self::MODULES) as $module) {
            Setting::setShowProductImages($module, $request->boolean('img_products_' . $module));
            Setting::setShowCategoryImages($module, $request->boolean('img_categories_' . $module));
            Setting::setCategoriesOnly($module, $request->boolean('catonly_' . $module));
        }

        return redirect()->route('catalog-images.edit')
            ->with('success', 'Configuración de imágenes guardada correctamente.');
    }
}
